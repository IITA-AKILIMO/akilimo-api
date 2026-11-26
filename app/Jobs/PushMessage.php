<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Jobs;

use App\Enums\PushStatus;
use App\Jobs\Core\AbstractJob;
use App\Repositories\MessageRepo;
use App\Repositories\PushDeviceRepo;
use App\Service\Fcm\FcmClient;
use Throwable;

/**
 * Pushes one stored message through FCM. Nothing in the API calls FCM during a request: the message is stored first and
 * this job (one per message for a topic, one per phone for a private message) does the push on the queue worker, where
 * FCM or network trouble is retried and never slows or fails the admin's request. The outcome is written to the message
 * (`push_status`: pushed, failed or not_pushed, see {@see PushStatus}). A failed push never loses the message: phones
 * still fetch it through the sync (GET /v1/messages).
 */
class PushMessage extends AbstractJob
{
    /** FCM trouble is often short-lived, so wait longer than the default before each of the two retries (the message stays stored for the sync). */
    public array $backoff = [30, 120];

    protected string $failureLevel = 'warning';

    protected string $failureMessage = 'Message push failed';

    /** @param int|null $deviceId the phone to push to (a private message), or null to push to the message's topics */
    public function __construct(public string $messageId, public ?int $deviceId = null) {}

    public function handle(FcmClient $fcm, MessageRepo $messages, PushDeviceRepo $devices): void
    {
        $skip = fn (string $reason) => $messages->markPush($this->messageId, PushStatus::NotPushed, $reason);

        if (!$fcm->isConfigured()) {
            $skip('FCM is not configured.');

            return;
        }
        $message = $messages->find($this->messageId);
        if ($message === null) {
            return;
        }
        if ($message->expires_at !== null && $message->expires_at->isPast()) {
            $skip('The message expired before it was pushed.');

            return;
        }

        $data = [
            'id' => $message->id,
            'type' => $message->type->value,
            'title' => $message->title,
            'body' => $message->body,
            'target' => $message->target,
        ];

        if ($this->deviceId !== null) {
            $device = $devices->find($this->deviceId);
            if ($device === null) {
                $skip('The phone is no longer registered.');

                return;
            }
            // FCM says the app is gone or the token expired: the phone was removed from push_devices
            if (!$fcm->sendToToken($device->fcm_token, $data)) {
                $skip('FCM reported the phone token as no longer valid.');

                return;
            }
        } else {
            $target = FcmClient::target($message->country, $message->language);
            if ($target === null) {
                $skip('The message has no country or language to push to.');

                return;
            }
            $fcm->send($target, $data);
        }

        $messages->markPush($this->messageId, PushStatus::Pushed);
    }

    /** All attempts used: the message is marked failed (unless it already reached a phone) and the failure is logged. */
    public function failed(Throwable $exception): void
    {
        app(MessageRepo::class)->markPush($this->messageId, PushStatus::Failed, $exception->getMessage());

        parent::failed($exception);
    }

    protected function failureContext(): array
    {
        return ['message_id' => $this->messageId, 'device_id' => $this->deviceId];
    }
}
