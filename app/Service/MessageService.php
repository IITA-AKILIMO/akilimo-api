<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Models\Message;
use App\Repositories\MessageRepo;
use App\Service\Fcm\FcmClient;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Stores a message for the mobile app and announces it through FCM. */
class MessageService
{
    public function __construct(
        private readonly MessageRepo $messages,
        private readonly FcmClient $fcm,
        private readonly PushDeviceService $devices,
    ) {}

    /**
     * Saves the message first, then tries to push it. A push that cannot be sent (FCM off, no audience, FCM error) never
     * loses the message: phones still get it through the sync.
     *
     * @param array<string, mixed> $attributes
     * @return array{message: Message, push: string} push is one of: sent, disabled, no_audience, failed
     */
    public function publish(array $attributes, ?int $createdBy): array
    {
        /** @var Message $message */
        $message = $this->messages->create([...$attributes, 'created_by' => $createdBy]);

        $target = FcmClient::target($message->country, $message->language);
        if ($target === null) {
            return ['message' => $message, 'push' => 'no_audience'];
        }
        if (!$this->fcm->isConfigured()) {
            return ['message' => $message, 'push' => 'disabled'];
        }

        try {
            $this->fcm->send($target, [
                'id' => $message->id,
                'type' => $message->type->value,
                'title' => $message->title,
                'body' => $message->body,
                'target' => $message->target,
            ]);
        } catch (Throwable $e) {
            Log::warning('Message push failed', ['message_id' => $message->id, 'error' => $e->getMessage()]);

            return ['message' => $message, 'push' => 'failed'];
        }

        $message->forceFill(['pushed_at' => now()])->save();

        return ['message' => $message, 'push' => 'sent'];
    }

    /**
     * A private message: one stored message per recipient (a farmer account or one phone), each pushed to every phone
     * the recipient has registered. Stored first, like {@see publish()}: a recipient with no registered phone, or a push
     * that fails, still finds the message on its next sync (GET /v1/messages with its device_token).
     *
     * @param array<string, mixed> $attributes message fields plus `recipients`: list of {type: user|device, id}
     * @return array{messages: array<int, Message>, push: string, devices: int} push is sent when at least one phone was pushed, else the first reason
     */
    public function publishPrivate(array $attributes, ?int $createdBy): array
    {
        $recipients = $attributes['recipients'];
        unset($attributes['recipients'], $attributes['country'], $attributes['language']);

        $messages = [];
        $pushed = 0;
        $reason = null;

        foreach ($recipients as $recipient) {
            /** @var Message $message */
            $message = $this->messages->create([
                ...$attributes,
                'created_by' => $createdBy,
                'recipient_user_id' => $recipient['type'] === 'user' ? (int)$recipient['id'] : null,
                'recipient_device_token' => $recipient['type'] === 'device' ? (string)$recipient['id'] : null,
            ]);
            $messages[] = $message;

            $count = 0;
            $status = 'no_audience';
            if (!$this->fcm->isConfigured()) {
                $status = 'disabled';
            } else {
                foreach ($this->devices->forRecipient($recipient) as $device) {
                    try {
                        $count += $this->fcm->sendToToken($device->fcm_token, [
                            'id' => $message->id,
                            'type' => $message->type->value,
                            'title' => $message->title,
                            'body' => $message->body,
                            'target' => $message->target,
                        ]) ? 1 : 0;
                        $status = $count > 0 ? 'sent' : $status;
                    } catch (Throwable $e) {
                        Log::warning('Message push failed', ['message_id' => $message->id, 'device_id' => $device->id, 'error' => $e->getMessage()]);
                        $status = $count > 0 ? 'sent' : 'failed';
                    }
                }
            }

            if ($count > 0) {
                $message->forceFill(['pushed_at' => now()])->save();
            }
            $pushed += $count;
            $reason ??= $status === 'sent' ? null : $status;
        }

        return ['messages' => $messages, 'push' => $pushed > 0 ? 'sent' : ($reason ?? 'no_audience'), 'devices' => $pushed];
    }
}
