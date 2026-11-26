# Messages for the mobile app

*IITA Internal Use*

Admins write messages; the API stores them and announces them to phones through Firebase Cloud Messaging (FCM): to **topics** (everyone in a country and/or language) or to **individual phones** (a farmer account or one install). Phones also fetch what they missed, so the app's message history is complete even when push was off or the phone was switched off. The Android side is described in the app repository's `docs/PUSH_NOTIFICATIONS.md`.

## How a message travels

1. An admin calls `POST /api/v1/admin/messages`.
2. The API **stores the message first** (table `messages`, uuid id), then **queues** the push (a `PushMessage` job). **No request ever calls FCM**: a queue worker sends it (see "The queue").
3. The push is a data-only FCM message to a **topic** (no device tokens): `country_<CODE>` and/or `lang_<code>`, the topics the app subscribes itself to. With both a country and a language the target is the condition `'country_TZ' in topics && 'lang_sw' in topics`.
4. The app saves the pushed message in its inbox, keyed by the message `id`.
5. On its next sync the app calls `GET /api/v1/messages?country=&language=&since=` and saves anything it does not have yet. Same `id`, stored once.

A failed or disabled push never loses a message: it is already stored and reaches phones through step 5.

### Private messages (one farmer or one phone)

Topics cannot address one person, so a message can instead have a **recipient**: a farmer account (`recipient_user_id`, reaches every phone signed in to it) or one install (`recipient_device_token`). One stored message per recipient. The API pushes it to each registered phone's FCM token (one push per phone), and `GET /api/v1/messages` returns it only to that recipient (see below). A private message has no country or language.

1. The app registers its FCM token with `PUT /api/v1/devices` (see "Phones").
2. An admin picks recipients (API: `recipients`; admin page: search farmers by name, phone or email, or a device token) and sends.
3. The API stores each message first, then pushes it to the recipient's phones. A token FCM reports as `UNREGISTERED` (app uninstalled, token expired) is deleted from `push_devices`.
4. A recipient with no registered phone, or a failed push, still gets the message on its next sync.

## Phones: `push_devices`

One row per install, keyed by `device_token`: the install id the app already sends with account and compute requests. It holds `fcm_token`, `platform` (`android`), `app_version`, `country`, `language`, `user_id` (the signed-in farmer, if any) and `last_seen_at`. A phone is only listed while the farmer has notifications switched on. **FCM tokens are personal data** (privacy policy wording needed, see "For IITA review").

| Method and path | Auth | Purpose |
|---|---|---|
| `PUT /api/v1/devices` | none, throttle `write`; an optional farmer Bearer token links the phone to the account (`farmer.optional`; the farmer must have given consent) | Register or refresh a phone |
| `DELETE /api/v1/devices` | none, throttle `write` | Unregister a phone (sign-out, notifications off). Answers `204` whether or not it was listed |

`PUT` body: `device_token` (required, up to 255), `fcm_token` (required, up to 512), `platform` (`android`), `app_version`, `country` (two letters), `language` (2 or 3 letters). A repeat updates the row; without a farmer token the account link is cleared; the same `fcm_token` under another `device_token` (a reinstall) removes the older row. Answer `200`: `data` with `device_token`, `platform`, `app_version`, `country`, `language`, `linked_to_account`, `last_seen_at`. `DELETE` body: `device_token`.

The app calls `PUT` at start, when Firebase gives a new token, and when country, language, account or the notifications switch change; `DELETE` on sign-out and when notifications are switched off.

## Endpoints

| Method and path | Auth | Purpose |
|---|---|---|
| `GET /api/v1/messages` | none (throttle `read`) | What a phone should have |
| `GET /api/v1/admin/messages` | `write` ability | All messages, newest first (`per_page` up to 100) |
| `POST /api/v1/admin/messages` | `write` ability (throttle `admin-write`) | Create and push a message |

### `GET /api/v1/messages`

Query: `country` (two letters), `language` (2 or 3 letters), `since` (ISO 8601), `limit` (1 to 200, default 100), `device_token` (the phone's install id). Returns messages for that country or any country, and that language or any language, not expired, **oldest first**, created at or after `since` (inclusive: a repeat is harmless). Pass the `created_at` of the last message you got as the next `since`. No `country` or `language` means only the messages addressed to everyone in that dimension. **Private messages are never returned to anyone else:** without `device_token` only broadcasts come back; with it, also the messages addressed to that `device_token` and to the farmer account that phone is registered under (`push_devices.user_id`).

```json
{ "data": [ {
  "id": "0b8e2c4e-7d0e-4f4e-9c55-6d2f5f1d3a10", "type": "advice",
  "title": "Bei mpya za muhogo", "body": "Bei ya muhogo imebadilika.",
  "target": "recommendations", "country": "TZ", "language": "sw",
  "created_at": "2026-11-21T09:30:00+00:00", "expires_at": null
} ] }
```

### `POST /api/v1/admin/messages`

| Field | Rules |
|---|---|
| `type` | required: `advice`, `reminder` or `announcement` (one notification channel each in the app) |
| `title` | required, up to 120 characters; write it in the language of the audience |
| `body` | required, up to 2000 characters |
| `country` | optional, an existing country code (case does not matter) |
| `language` | optional, 2 or 3 letters (case does not matter) |
| `target` | optional: `hub`, `recommendations`, `settings`, `inbox`; empty means the type's default screen |
| `expires_at` | optional, a future time; after it the sync stops returning the message |
| `recipients` | optional, up to 100 of `{"type": "user", "id": 12}` (a farmer account, role `user`) or `{"type": "device", "id": "<device_token>"}` (a registered phone). Makes the message private; `country` and `language` are then not allowed |

Answer `201`: `data` (the message), `message`, and **`push`**, one of:

- `queued`: the push is queued for the queue worker, which sends it and sets `pushed_at` (a failure is retried, then logged as "Message push failed").
- `disabled`: FCM is not configured; stored for the sync only.
- `no_audience`: no country and no language. There is no topic for everyone, so it reaches phones through the sync only.

With `recipients` the answer is `201` with `data` (a list, one message per recipient), `devices` (phones queued, one job each) and `push`: `queued` when at least one phone was queued, otherwise the first reason nothing was (`disabled`, `no_audience` meaning no registered phone).

Send one message per language so the text matches the audience (`lang_sw` in Swahili, `lang_rw` in Kinyarwanda).

## Configuration

`config/fcm.php`, set in `.env`:

```
FCM_ENABLED=false          # true once a service account is set
FCM_PROJECT_ID=            # Firebase project id (same project as the app's google-services.json)
FCM_CREDENTIALS=           # path to the service account JSON, or the JSON itself
FCM_TIMEOUT=10             # seconds
```

### Getting the credentials

The API needs a Firebase **service account key** (a JSON file). The app needs nothing extra: it already has `google-services.json`.

1. Open the Firebase console (https://console.firebase.google.com) and pick the **same project** as the app's `google-services.json`. Its `project_id` is `FCM_PROJECT_ID`.
2. Gear icon > Project settings > **Service accounts** > **Generate new private key**. A `.json` file downloads; Firebase keeps no copy, so store it safely (IITA keeps its keys in the shared keystores drive). You need the Owner or Editor role on the project to see the tab.
3. The key's account must be allowed to send messages. The default `firebase-adminsdk` account is. For a dedicated account, create it in Google Cloud IAM with the role **Firebase Cloud Messaging API Admin**.
4. Make sure the **Firebase Cloud Messaging API (V1)** is enabled for the project in Google Cloud (on by default in newer projects).

Treat the file like a password: never commit it, never paste it in chat or tickets. A token from another Firebase project is rejected by FCM, so the project id and the app's `google-services.json` must match.

The API signs a short-lived JWT with the key (needs `ext-openssl`), exchanges it for an access token, and caches the token for 55 minutes. No extra package is needed.

### Local development

Add to `.env` (gitignored), with forward slashes and the exact file name, then `php artisan config:clear`:

```
FCM_ENABLED=true
FCM_PROJECT_ID=<project_id from google-services.json>
FCM_CREDENTIALS=G:/My Drive/keystores/service_accounts/akilimo/<file name>.json
```

### Production (Dokploy)

Do not point at a Drive path in production. Either way, redeploy afterwards, run `php artisan migrate --force`, and restart or `php artisan config:clear` if the image caches config.

- **File mount (recommended).** Application > Advanced > Mounts > Add Mount, type *File Mount*; file path `fcm-service-account.json`, content = the whole JSON; container path e.g. `/run/secrets/fcm-service-account.json`. In the Environment tab set `FCM_ENABLED=true`, `FCM_PROJECT_ID=<project_id>` and `FCM_CREDENTIALS=/run/secrets/fcm-service-account.json`.
- **JSON in the variable.** `FCM_CREDENTIALS` also accepts the JSON itself: one line, wrapped in single quotes, keeping the `\n` sequences inside `private_key` exactly as in the file (`FCM_CREDENTIALS='{"type":"service_account", ...}'`).

### Checking it works

Run `php artisan fcm:check` (in production: `docker exec` / the Dokploy terminal). It checks, in order, that `FCM_ENABLED`, `FCM_PROJECT_ID` and `FCM_CREDENTIALS` are set, that the key is a readable service account (prints its e-mail, never the key), that Google issues an access token and that FCM accepts a message (`validate_only`: nothing is delivered). Add `--device=<device_token>` to also check that registered phone's FCM token, and `--send` to really deliver a test notification to it. It exits with a failure and FCM's own answer when something is wrong.

If a value contains spaces (a path such as `G:/My Drive/...`), quote it in `.env`: `FCM_CREDENTIALS="G:/My Drive/..."`; an unquoted one stops the whole app from starting.

Or check by hand:

1. Install an app build and switch notifications on, so the phone registers (`push_devices` gets a row).
2. In `/admin/messages`, choose "Specific farmers or phones", add that device and send. The banner should say the push to 1 phone is queued. With a queue worker running (`php artisan queue:work`) the phone gets it within seconds.
3. "Push is switched off" means `FCM_ENABLED` or the credentials are not being read (check the variables reached the container, clear cached config). A push that fails in the worker is retried, then logged as "Message push failed": the usual causes are a wrong `FCM_PROJECT_ID` or a service account without the messaging role.

## Push status

Every message carries `push_status`, set when it is stored and updated by the push job, so it reflects what really happened and not only that a push was queued (admin endpoints and the admin page show it; phones never get it):

| `push_status` | Meaning |
|---|---|
| `queued` | a push job is waiting for, or running on, a queue worker |
| `pushed` | FCM accepted the push (for a private message: for at least one phone); `pushed_at` is set |
| `failed` | the job used all its attempts and FCM never accepted it; `push_error` says why |
| `not_pushed` | nothing was or could be sent; `push_error` says why: FCM off, no country or language, no registered phone, the message expired, or the phone token is no longer valid |

For a private message with several phones the best outcome wins: one phone reached means `pushed`, whatever happened to the others. A failure only replaces `queued` or `not_pushed`. Messages stored before this column existed are `pushed` when `pushed_at` was set, else `not_pushed`.

## The queue

Every push is a queued job; the API never calls FCM while answering a request. `MessageService` stores the message and dispatches `App\Jobs\PushMessage` (one job per message for a topic, one per phone for a private message; 3 tries, retried after 30 s and 120 s). A **queue worker must be running** (`php artisan queue:work`; production runs one under supervisor, `QUEUE_CONNECTION=redis`) or nothing is pushed. Messages stay stored either way, so phones still fetch them through the sync. Even `php artisan fcm:check --send` queues its test message.

## Automatic notices when an admin changes something

An admin change queues a notice for farmers (`config/farmer-notifications.php`); nothing else is needed on the admin pages.

| Change | Notice (`type`) | Audience |
|---|---|---|
| A price in `price_catalogue` becomes active, or an active price's price changes | `prices` (advice) | that country |
| A country's fertilizer is added, removed or its availability changes | `fertilizers` (advice) | that country |
| A starch factory or starch price is added, changed or removed | `starch` (advice) | that country |
| The app's `latest` version is raised | `app_update` (announcement) | everyone |
| The app's `min_supported` version is raised | `app_forced_update` (announcement) | everyone |

How it works:
- The change only queues a delayed `SendChangeNotice` job (`FARMER_NOTIFICATIONS_DEBOUNCE_MINUTES`, default 10). The job is unique per kind and country until it starts, so a batch of edits (a price import, a batch edit) is **one** notice.
- The job stores one message per language (`en`, `sw`, `rw`, the `languages` list in the config; texts in `lang/<code>/farmer_notice.php`), each pushed to `country_XX` + `lang_xx` (or just `lang_xx` for app updates). The Kinyarwanda and Swahili wording is a draft and should be checked by native speakers.
- Messages have no `target`, so a tap opens the app's Messages inbox, and they expire after `FARMER_NOTIFICATIONS_EXPIRES_DAYS` (14).
- Changes made by artisan commands, seeders and migrations never notify (`FARMER_NOTIFICATIONS_IN_CONSOLE=false`). Switch everything off with `FARMER_NOTIFICATIONS_ENABLED=false`.
- A draft, or a price waiting for a second review, does not notify: only what farmers are served does.

After raising `FARMER_CONSENT_VERSION`, run `php artisan farmers:notify-consent` (`--dry-run` first): every farmer on an older version with a registered phone gets one private message in the language of their phone, queued like any other, once per version.

## Admin page

`/admin/messages` (admin role) has the same form. Under "Specific farmers or phones" an admin searches farmer accounts by name, email or the phone number on their advice requests, or a registered phone by device token, adds them, and sees how many phones the message will reach (`GET /admin/messages/recipients?q=`, `GET /admin/messages/audience`). The list of sent messages shows the audience or the (masked) recipient.

## Deploying

Run the migrations (`push_devices`, and the recipient columns on `messages`) **before** releasing the app version that calls `PUT /v1/devices`; an older API answers 404 and the app retries at its next start.

## For IITA review

- FCM tokens and the install id are stored on the server and, for a signed-in farmer, linked to the account. The privacy policy (`docs/policies/`) needs wording for this: what is stored (FCM token, install id, app version, country, language, account link, last seen), why (to send messages to that farmer or phone), and that switching notifications off or signing out removes it.
- Retention: a row is removed on unregister and when FCM reports the token `UNREGISTERED`; deleting an account clears the link (`user_id` becomes null) and removes messages addressed to it. Decide whether to purge phones not seen for a long time.

## Not covered

- Read receipts: the app keeps read state on the phone.
- An account message created before the phone signed in: a phone whose sync cursor is already past it does not fetch it.
- Editing or recalling a message after it was sent: a push cannot be recalled, and phones that already synced keep their copy. Set `expires_at` for messages that should stop being fetched.
