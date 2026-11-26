<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Enums\PushStatus;
use App\Jobs\PushMessage;
use App\Models\Message;
use App\Repositories\MessageRepo;
use App\Service\Fcm\FcmClient;

/**
 * Stores a message for the mobile app and queues its push. Nothing here calls FCM: the push is a {@see PushMessage} job
 * that a queue worker runs, so a slow or failing FCM never touches the request that created the message.
 */
class MessageService
{
    public function __construct(
        private readonly MessageRepo $messages,
        private readonly FcmClient $fcm,
        private readonly PushDeviceService $devices,
    ) {}

    /**
     * Saves the message first, then queues the push to its country and/or language topics. A push that cannot be queued
     * (FCM off, no audience) or fails later never loses the message: phones still get it through the sync.
     *
     * @param array<string, mixed> $attributes
     * @return array{message: Message, push: string} push is one of: queued, disabled, no_audience
     */
    public function publish(array $attributes, ?int $createdBy): array
    {
        $reason = match (true) {
            FcmClient::target($attributes['country'] ?? null, $attributes['language'] ?? null) === null => 'no_audience',
            !$this->fcm->isConfigured() => 'disabled',
            default => null,
        };

        /** @var Message $message */
        $message = $this->messages->create([
            ...$attributes,
            'created_by' => $createdBy,
            'push_status' => $reason === null ? PushStatus::Queued : PushStatus::NotPushed,
            'push_error' => match ($reason) {
                'no_audience' => 'The message has no country or language to push to.',
                'disabled' => 'FCM is not configured.',
                default => null,
            },
        ]);

        if ($reason !== null) {
            return ['message' => $message, 'push' => $reason];
        }

        PushMessage::dispatch($message->id);

        return ['message' => $message, 'push' => 'queued'];
    }

    /**
     * A private message: one stored message per recipient (a farmer account or one phone), each queued for every phone
     * the recipient has registered (one job per phone). Stored first, like {@see publish()}: a recipient with no
     * registered phone still finds the message on its next sync (GET /v1/messages with its device_token).
     *
     * @param array<string, mixed> $attributes message fields plus `recipients`: list of {type: user|device, id}
     * @return array{messages: array<int, Message>, push: string, devices: int} push is queued when at least one phone was queued, else the first reason
     */
    public function publishPrivate(array $attributes, ?int $createdBy): array
    {
        $recipients = $attributes['recipients'];
        unset($attributes['recipients'], $attributes['country'], $attributes['language']);

        $messages = [];
        $queued = 0;
        $reason = null;

        foreach ($recipients as $recipient) {
            $devices = $this->devices->forRecipient($recipient);
            $skipped = match (true) {
                !$this->fcm->isConfigured() => 'disabled',
                $devices->isEmpty() => 'no_audience',
                default => null,
            };

            /** @var Message $message */
            $message = $this->messages->create([
                ...$attributes,
                'created_by' => $createdBy,
                'recipient_user_id' => $recipient['type'] === 'user' ? (int)$recipient['id'] : null,
                'recipient_device_token' => $recipient['type'] === 'device' ? (string)$recipient['id'] : null,
                'push_status' => $skipped === null ? PushStatus::Queued : PushStatus::NotPushed,
                'push_error' => match ($skipped) {
                    'disabled' => 'FCM is not configured.',
                    'no_audience' => 'The recipient has no phone registered for push.',
                    default => null,
                },
            ]);
            $messages[] = $message;

            if ($skipped !== null) {
                $reason ??= $skipped;

                continue;
            }
            foreach ($devices as $device) {
                PushMessage::dispatch($message->id, $device->id);
                $queued++;
            }
        }

        return ['messages' => $messages, 'push' => $queued > 0 ? 'queued' : ($reason ?? 'no_audience'), 'devices' => $queued];
    }
}
