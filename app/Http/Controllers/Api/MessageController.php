<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MessageRequest;
use App\Http\Resources\MessageResource;
use App\Repositories\MessageRepo;
use App\Service\MessageService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    public function __construct(
        protected MessageRepo $messageRepo,
        protected MessageService $messageService,
    ) {}

    /**
     * Messages for a phone
     *
     * What the mobile app fetches during its sync: messages for the given country and language (and those for everyone),
     * not expired, oldest first, from `since` on (inclusive; a repeat is harmless because the app stores each id once).
     * Pass the `created_at` of the last message received as the next `since`. With `device_token`, messages addressed to
     * that phone (or to the farmer account it is signed in to) are included; they are never returned to anyone else.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'country', description: 'Two-letter country code, e.g. TZ.', type: 'string')]
    #[QueryParameter(name: 'language', description: 'Language code, e.g. sw.', type: 'string')]
    #[QueryParameter(name: 'since', description: 'ISO 8601 time; only messages created at or after it.', type: 'string')]
    #[QueryParameter(name: 'device_token', description: "The phone's install id; adds the messages private to it or to the account it is signed in to.", type: 'string')]
    #[QueryParameter(name: 'limit', description: 'Most messages to return (1 to 200, default 100).', type: 'int')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->validate([
            'country' => ['nullable', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'language' => ['nullable', 'string', 'regex:/^[A-Za-z]{2,3}$/'],
            'since' => ['nullable', 'date'],
            'device_token' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $messages = $this->messageRepo->forAudience(
            country: isset($query['country']) ? strtoupper($query['country']) : null,
            language: isset($query['language']) ? strtolower($query['language']) : null,
            since: isset($query['since']) ? now()->parse($query['since']) : null,
            limit: (int)($query['limit'] ?? 100),
            deviceToken: $query['device_token'] ?? null,
        );

        return MessageResource::collection($messages);
    }

    /**
     * List all messages (admin)
     *
     * Every message ever sent, newest first, whatever its audience.
     */
    public function adminIndex(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int)$request->integer('per_page', 25), 1), 100);

        return MessageResource::collection(
            $this->messageRepo->paginateWithSort(perPage: $perPage, orderBy: 'created_at', direction: 'desc'),
        );
    }

    /**
     * Send a message (admin)
     *
     * Stores a message for the mobile app and announces it through FCM to the country and language topics. A push that
     * cannot be sent never loses the message: phones still receive it through the sync. `push` in the answer says what
     * happened: `sent`, `disabled` (FCM not configured), `no_audience` (no country or language, stored for the sync only)
     * or `failed`.
     *
     * With `recipients` (`[{type: "user", id: 12}, {type: "device", id: "<device_token>"}]`) the message is private: one
     * message per recipient, pushed to each of their registered phones (`country` and `language` are not allowed).
     * `data` is then a list, `push` is `sent` when at least one phone was pushed (else the first reason a push did not
     * go) and `devices` counts the phones pushed. `no_audience` here means no registered phone.
     */
    public function store(MessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['recipients'])) {
            $result = $this->messageService->publishPrivate($data, $request->user()?->id);

            return response()->json([
                'data' => MessageResource::collection($result['messages']),
                'push' => $result['push'],
                'devices' => $result['devices'],
                'message' => 'Messages created.',
            ], 201);
        }

        $result = $this->messageService->publish($data, $request->user()?->id);

        /**
         * @status 201
         */
        return response()->json([
            'data' => new MessageResource($result['message']),
            'push' => $result['push'],
            'message' => 'Message created.',
        ], 201);
    }
}
