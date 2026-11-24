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
}
