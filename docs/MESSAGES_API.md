# Messages for the mobile app

*IITA Internal Use*

Admins write messages; the API stores them and announces them to phones through Firebase Cloud Messaging (FCM). Phones also fetch what they missed, so the app's message history is complete even when push was off or the phone was switched off. The Android side is described in the app repository's `docs/PUSH_NOTIFICATIONS.md`.

## How a message travels

1. An admin calls `POST /api/v1/admin/messages`.
2. The API **stores the message first** (table `messages`, uuid id), then tries to push it.
3. The push is a data-only FCM message to a **topic** (no device tokens): `country_<CODE>` and/or `lang_<code>`, the topics the app subscribes itself to. With both a country and a language the target is the condition `'country_TZ' in topics && 'lang_sw' in topics`.
4. The app saves the pushed message in its inbox, keyed by the message `id`.
5. On its next sync the app calls `GET /api/v1/messages?country=&language=&since=` and saves anything it does not have yet. Same `id`, stored once.

A failed or disabled push never loses a message: it is already stored and reaches phones through step 5.

## Endpoints

| Method and path | Auth | Purpose |
|---|---|---|
| `GET /api/v1/messages` | none (throttle `read`) | What a phone should have |
| `GET /api/v1/admin/messages` | `write` ability | All messages, newest first (`per_page` up to 100) |
| `POST /api/v1/admin/messages` | `write` ability (throttle `admin-write`) | Create and push a message |

### `GET /api/v1/messages`

Query: `country` (two letters), `language` (2 or 3 letters), `since` (ISO 8601), `limit` (1 to 200, default 100). Returns messages for that country or any country, and that language or any language, not expired, **oldest first**, created at or after `since` (inclusive: a repeat is harmless). Pass the `created_at` of the last message you got as the next `since`. No `country` or `language` means only the messages addressed to everyone in that dimension.

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

Answer `201`: `data` (the message), `message`, and **`push`**, one of:

- `sent`: FCM accepted it (`pushed_at` is set).
- `disabled`: FCM is not configured; stored for the sync only.
- `no_audience`: no country and no language. There is no topic for everyone, so it reaches phones through the sync only.
- `failed`: FCM refused it or could not be reached (logged as "Message push failed"); stored for the sync.

Send one message per language so the text matches the audience (`lang_sw` in Swahili, `lang_rw` in Kinyarwanda).

## Configuration

`config/fcm.php`, set in `.env`:

```
FCM_ENABLED=false          # true once a service account is set
FCM_PROJECT_ID=            # Firebase project id (same project as the app's google-services.json)
FCM_CREDENTIALS=           # path to the service account JSON, or the JSON itself
FCM_TIMEOUT=10             # seconds
```

Service account: in the Firebase project's settings create a key for an account that may send messages (role "Firebase Cloud Messaging API Admin"), and keep the JSON out of the repository (mount it as a secret and point `FCM_CREDENTIALS` at it). The API signs a short-lived JWT with it (needs `ext-openssl`), exchanges it for an access token, and caches the token for 55 minutes. No extra package is needed. The Cloud Messaging API (V1) must be enabled in the project.

## Not covered

- Per-farmer targeting and read receipts: topics cannot address one farmer, and the app keeps read state on the phone. Both need an install or device concept and the FCM token stored here.
- Editing or recalling a message after it was sent: a push cannot be recalled, and phones that already synced keep their copy. Set `expires_at` for messages that should stop being fetched.
- A queue: the push runs inside the admin request (`QUEUE_CONNECTION` is not used). For very frequent sends, move `MessageService::publish`'s push into a job.
