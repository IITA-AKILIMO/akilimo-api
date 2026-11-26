<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Admin;

use App\Enums\EnumMessageType;
use App\Enums\EnumUserRole;
use App\Http\Requests\Admin\MessageRequest;
use App\Models\ApiRequest;
use App\Models\Message;
use App\Models\PushDevice;
use App\Models\User;
use App\Repositories\PushDeviceRepo;
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
        'sent' => 'Message saved and sent to phones.',
        'disabled' => 'Message saved. Push is switched off, so phones get it the next time they open the app.',
        'no_audience' => 'Message saved. It has no country or language, so phones get it the next time they open the app.',
        'failed' => 'Message saved, but the push failed. Phones get it the next time they open the app.',
    ];

    private const array PRIVATE_PUSH_RESULT = [
        'sent' => 'Messages saved and sent to %d phone(s).',
        'disabled' => 'Messages saved. Push is switched off, so recipients get them the next time they open the app.',
        'no_audience' => 'Messages saved. None of the recipients has a phone registered for push, so they get them the next time they open the app.',
        'failed' => 'Messages saved, but the push failed. Recipients get them the next time they open the app.',
    ];

    public function __construct(protected MessageService $service, protected PushDeviceService $devices, protected PushDeviceRepo $deviceRepo) {}

    public function index(Request $request): Response
    {
        $paginator = Message::query()->latest('created_at')
            ->paginate(min(max((int)$request->integer('per_page', 15), 1), 100))
            ->withQueryString();

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
                ->with($result['push'] === 'failed' ? 'error' : 'success', sprintf(self::PRIVATE_PUSH_RESULT[$result['push']], $result['devices']));
        }

        $result = $this->service->publish($data, $request->user()?->id);

        return redirect()->route('admin.messages.index')
            ->with($result['push'] === 'failed' ? 'error' : 'success', self::PUSH_RESULT[$result['push']]);
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
        $escaped = addcslashes($q, '%_\\');

        $users = User::query()->where('role', EnumUserRole::User)
            ->where(fn ($w) => $w->where('name', 'like', "%{$escaped}%")->orWhere('email', 'like', "%{$escaped}%")->orWhere('username', 'like', "%{$escaped}%")
                ->orWhereIn('id', ApiRequest::query()->whereNotNull('user_id')->where('phone_number', 'like', "%{$escaped}%")->select('user_id')))
            ->orderBy('name')->limit(10)->get();

        $phones = ApiRequest::query()->whereIn('user_id', $users->modelKeys())->whereNotNull('phone_number')->orderBy('created_at')
            ->get(['user_id', 'phone_number'])->groupBy('user_id')->map(fn ($rows) => $rows->last()->phone_number);

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
