<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Enums\EnumMessageType;
use App\Http\Requests\Admin\MessageRequest;
use App\Models\Message;
use App\Models\PushDevice;
use App\Models\User;
use App\Repositories\ApiRequestRepo;
use App\Repositories\MessageRepo;
use App\Repositories\PushDeviceRepo;
use App\Repositories\UserRepo;
use App\Service\MessageService;
use App\Service\PushDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Where an admin writes a message for the mobile app and sees what was sent.
 * The same rules and push as the API's admin endpoint (MessageService).
 */
class MessageController extends AdminController
{
    private const array PUSH_RESULT = [
        'queued' => 'Message saved. The push is queued and goes out as soon as a queue worker runs it.',
        'disabled' => 'Message saved. Push is switched off, so phones get it the next time they open the app.',
        'no_audience' => 'Message saved. It has no country or language, so phones get it the next time they open the app.',
    ];

    private const array PRIVATE_PUSH_RESULT = [
        'queued' => 'Messages saved. The push to %d phone(s) is queued and goes out as soon as a queue worker runs it.',
        'disabled' => 'Messages saved. Push is switched off, so recipients get them the next time they open the app.',
        'no_audience' => 'Messages saved. None of the recipients has a phone registered for push, so they get them the next time they open the app.',
    ];

    public function __construct(protected MessageService $service, protected PushDeviceService $devices, protected PushDeviceRepo $deviceRepo,
        protected MessageRepo $messageRepo,
        protected UserRepo $userRepo,
        protected ApiRequestRepo $requestRepo,
    ) {}

    public function index(Request $request): Response
    {
        $paginator = $this->messageRepo->latestPaginated(min(max((int)$request->integer('per_page', 15), 1), 100));

        return Inertia::render('Messages/Index', [
            'messages' => [
                'data' => collect($paginator->items())->map(fn (Message $m) => [
                    'id' => $m->id,
                    'type' => $m->type->value,
                    'title' => $m->title,
                    'body' => $m->body,
                    'country' => $m->country,
                    'language' => $m->language,
                    'target' => $m->target,
                    'recipient' => $m->recipient_user_id ? 'Account #' . $m->recipient_user_id : ($m->recipient_device_token ? 'Device ' . substr($m->recipient_device_token, 0, 8) . '****' : null),
                    'push_status' => $m->push_status->value,
                    'push_error' => $m->push_error,
                    'pushed_at' => $m->pushed_at?->toIso8601String(),
                    'expires_at' => $m->expires_at?->toIso8601String(),
                    'created_at' => $m->created_at?->toIso8601String(),
                ])->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
            'types' => EnumMessageType::values(),
            'targets' => EnumMessageType::TARGETS,
        ]);
    }

    public function store(MessageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (!empty($data['recipients'])) {
            $result = $this->service->publishPrivate($data, $request->user()?->id);

            return redirect()->route('admin.messages.index')
                ->with('success', sprintf(self::PRIVATE_PUSH_RESULT[$result['push']], $result['devices']));
        }

        $result = $this->service->publish($data, $request->user()?->id);

        return redirect()->route('admin.messages.index')
            ->with('success', self::PUSH_RESULT[$result['push']]);
    }

    /**
     * Farmer accounts (by name, email or the phone number on their advice requests) and registered phones (by device
     * token) that match a search, with how many phones each reaches.
     */
    public function recipients(Request $request): JsonResponse
    {
        $q = trim((string)$request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }
        $users = $this->userRepo->searchFarmers($q, $this->requestRepo->userIdsByPhone($q), 10);
        $phones = $this->requestRepo->latestPhones($users->modelKeys());

        $rows = $users->map(fn (User $u) => [
            'type' => 'user',
            'id' => (string)$u->id,
            'label' => $u->name,
            'detail' => collect([$u->email, $phones[$u->id] ?? null])->filter()->implode(' · '),
            'devices' => $this->devices->countFor([['type' => 'user', 'id' => $u->id]]),
        ]);

        $devices = $this->deviceRepo->searchByTokenPrefix($q, 10)
            ->map(fn (PushDevice $d) => [
                'type' => 'device',
                'id' => $d->device_token,
                'label' => 'Device ' . substr($d->device_token, 0, 8) . '****',
                'detail' => collect([$d->country, $d->language, $d->app_version ? 'v' . $d->app_version : null, $d->user_id ? 'signed in' : null])->filter()->implode(' · '),
                'devices' => 1,
            ]);

        return response()->json(['data' => $rows->concat($devices)->values()]);
    }

    /** How many distinct phones the chosen recipients reach. */
    public function audience(Request $request): JsonResponse
    {
        $recipients = $request->validate([
            'recipients' => ['required', 'array', 'max:100'],
            'recipients.*.type' => ['required', 'in:user,device'],
            'recipients.*.id' => ['required', 'string', 'max:255'],
        ])['recipients'];

        return response()->json(['devices' => $this->devices->countFor($recipients)]);
    }
}
