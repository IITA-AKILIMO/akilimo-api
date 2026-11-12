# Farmer accounts, Google sign-in and saved advice: design

**IITA Internal Use.** Draft for the owner, 2026-11-12. The decisions in section 8 were made by the IITA senior analyst and are final. Nothing here is built. Written from the mobile app side
(`akilimo-mobile`, branch `feat/ui-design-system`), which wants an optional account so a farmer can keep their advice
and open it again later. The API does the work; the app follows.

## 1. Goal and benefits

An optional, free account. The app keeps working without one. The reasons to sign up, as the app will say them
(wording is a placeholder until IITA has agreed it, see section 8):

- Save your advice and see it again any time.
- Keep your history if you change phone.
- Share saved advice with your extension worker.

## 2. What exists today (checked in the code)

- **Staff login:** `POST v1/auth/login` (username or email, plus password) returns a Bearer token
  (`AuthService::attempt`). Abilities come from the role: Admin gets `*`, Partner gets `PARTNER_ABILITIES`, anyone else gets
  `PLAYGROUND_ABILITIES` (`recommendations:compute` only). Tokens last `auth.token_ttl_days` (30) and are stored hashed.
- **Roles:** `EnumUserRole` has `admin`, `partner` and `user`. The `users` table has `name`, `username`, `email`, `role`,
  `email_verified_at`, `password`.
- **Web playground accounts:** `/playground/register` creates a `user` account (username, email, password of at least 8
  characters, confirmed). There is **no email verification, no password reset and no consent step**.
- **Playground history:** `ApiRequestRepo::playgroundHistory` returns a user's requests by
  `api_requests.device_token = 'playground-' . $userId`. So history is linked to a person only through that naming trick.
- **Compute is open:** `POST v1/recommendations/compute` has only `throttle:compute`; the `auth.token` line is commented out.
  The app sends `user_info.device_token`, a random UUID it creates on first run.
- **History list for partners:** `GET v1/recommendations` needs `recommendations:read` and is **not** scoped to one person.
- **Not present:** Google or any social sign-in (no Socialite or OAuth code), farmer sign-up through the JSON API, per-account
  history over the JSON API, account deletion, a real mailer (`MAIL_MAILER` defaults to `log`).

## 3. Proposed design

### 3.1 Accounts
- Reuse `users` with role `user`. Add `google_id` (nullable, unique). Make `password` nullable for Google-only accounts.
- New ability `history:own` (read and delete only your own saved advice). New preset `FARMER_ABILITIES`:
  `recommendations:compute` and `history:own`. Farmer tokens never get a broad or admin ability.
- Keep staff accounts (`admin`, `partner`) separate in behaviour: the farmer endpoints below only ever issue
  `FARMER_ABILITIES`, whatever the role.
- **Existing playground accounts may sign in to the app** (decision 2). They have no consent record, so their first app sign-in
  answers `consent_required`: the app shows the consent text and sends it to `POST /consent`; until then no history is linked
  or returned. Their old web history (`device_token = 'playground-<id>'`) is not moved unless they ask for it.

### 3.2 Endpoints (JSON, `v1/account`)
| Method and path | Purpose | Throttle |
|---|---|---|
| `POST /register` | email, password, display name, `consent` true, optional `device_token` | `sensitive` |
| `POST /login` | email and password, optional `device_token` | `auth` |
| `POST /google` | Google `id_token`, `consent` on first use, optional `device_token` | `auth` |
| `POST /consent` | record consent for an existing account (`consent_required` on first sign-in) | `write` |
| `POST /logout` | revoke the current token | `write` |
| `POST /email/verify`, `POST /email/resend` | confirm the email address | `sensitive` |
| `POST /password/forgot`, `POST /password/reset` | email-link reset | `sensitive` |
| `GET /history` | the caller's saved advice, newest first, paginated | `read` |
| `GET /history/{request_id}` | one saved recommendation with its result | `read` |
| `DELETE /history/{request_id}` | remove one saved item | `write` |
| `DELETE /` | delete the account and its saved advice | `sensitive` |

Responses follow the existing `login` shape: `token_type`, `token`, `expires_at`, `user`.
Document all of them in the OpenAPI (Scramble) like the rest.

### 3.3 Google sign-in
- The app gets a Google **ID token** with Android's Credential Manager and posts it to `/google`.
- The API verifies it: signature against Google's published keys, `iss` is Google, `aud` equals our OAuth **client ID**
  (a non-secret value in `.env` as `GOOGLE_CLIENT_ID`, one per Android and web client), `exp` not passed,
  `email_verified` true. Use a maintained library (for example `google/apiclient` or `firebase/php-jwt` with the key set).
- No client secret is needed for ID-token verification, so no secret goes to the app or into the repo.
- Match by `google_id`, else by verified email (link to an existing account only when the email is verified), else create.

### 3.4 Linking advice to an account
- Add `api_requests.user_id` (nullable, foreign key to `users`, indexed). Set it when `compute` arrives with a farmer
  Bearer token (make the auth on `compute` optional: anonymous use must keep working).
- **Claim this phone's earlier advice once:** on register or login with a `device_token`,
  `UPDATE api_requests SET user_id = ? WHERE device_token = ? AND user_id IS NULL`. The token is a random UUID known only to
  that phone. Without this, nothing the farmer did before signing up would be saved.
- Keep `playgroundHistory` working; later move it to `user_id` too and retire the `playground-<id>` naming.
- History responses return the same fields as the playground list plus the stored result (`plumber_response`).

### 3.5 Security
- Passwords: `Hash::make`, minimum 8 characters, a breached-password check if available. Never log passwords or tokens.
- Throttle per IP **and** per email on login, register, reset and verify, to limit guessing and mail abuse.
- Generic errors on login and reset ("wrong email or password"; "if the address exists we sent a link") so accounts cannot be listed.
- Tokens: farmer tokens last 30 days like today; the app renews by signing in again. Revoke all tokens on password change and delete.
- Mail: a real mailer must be configured (today `log`) before verification and reset can work in production.

### 3.6 Privacy and data
- A saved request holds the farm location, the answers and the result (`droid_request`, `plumber_request`,
  `plumber_response`). That is personal data once linked to an account.
- Store only what the history needs; no phone number or name is required to sign up (display name is optional).
- Consent is recorded at sign-up or first app sign-in (`consent_at`, wording version).
- **Retention:** saved advice is kept until the farmer deletes it; there is no scheduled purge. The farmer can delete one item or the whole account at any time.
- **Deleting an account** removes the account and its tokens. Their past requests are **kept for statistics with the link removed**:
  `api_requests.user_id` is set to null (`ON DELETE SET NULL`) and `device_token` is cleared on those rows. The location and answers
  stay in those rows, so keep them out of any public export.
- Engine logs already redact user fields; keep farmer emails out of every log (use the existing masking).

## 4. Tests (Pest, in the repo's style)
Register (valid, duplicate email, weak password, no consent); login (right, wrong, throttled); Google (valid, wrong `aud`,
expired, unverified email, link to an existing verified account); history only returns the caller's rows; claiming by
`device_token` happens once and never takes another account's rows; delete removes the account and its rows; farmer tokens
cannot reach admin, partner or other users' routes. Fake the Google key fetch so tests need no network.

## 5. Order of work
1. Migration (`users.google_id`, nullable password, consent columns; `api_requests.user_id`) and the `FARMER_ABILITIES` preset.
2. Register, login, logout, verify and reset by email (needs the mailer).
3. Google sign-in.
4. Optional auth on `compute`, `user_id` linking and the device-token claim.
5. History endpoints, deletion, OpenAPI and tests.
6. Then the app: sign-up and sign-in screens, Google through Credential Manager, token in encrypted storage, upload and
   saved-advice list that also works offline from Room, and a share action.

## 6. What the app will call
`/v1/account/register|login|google|logout`, `/v1/account/history` and `DELETE /v1/account`, with `device_token` on
register and login. The app sends nothing new to `compute` except the Bearer header when signed in.

## 7. Risks
- Personal data and consent obligations (section 8) are the main risk, not the code.
- Google setup needs a Google Cloud project and the Android client's package name and signing-certificate fingerprint.
- Optional auth on `compute` must not break the many anonymous callers (app, MCP, partners); roll it out with tests first.
- Linking by `device_token` assumes the token stays private; do not log it.

## 8. Decisions (final: IITA senior analyst, 2026-11-12)
1. **Sign-in:** email and password **and** Google sign-in. So email verification and password reset are both in scope.
2. **Existing playground accounts** may also sign in to the app (same `users` table), after giving consent on first app sign-in (section 3.1).
3. **Email:** a **hosted email service** sends verification and reset emails. The Laravel mailer is configured by environment
   variables only; the provider (for example Amazon SES or Postmark), its account and a verified sending domain are still to be named and set up.
   Until then the mailer stays on `log`, and verification and reset stay off in production.
4. **Retention:** saved advice is kept until the farmer deletes it. No scheduled purge.
5. **After account deletion:** past requests are kept as anonymous statistics with the link to the person removed (section 3.6).
6. **Google Cloud:** the owner creates the project and the OAuth client IDs and supplies the non-secret client IDs and the debug and
   release signing fingerprints. No secret is ever shared with the app or committed. Until then Google sign-in is built against a
   test client ID in config and stays off.
7. **Where it is built:** `new-akilimo-api` (this repo) for the API, `akilimo-mobile` for the app. *(Assumed from the plan; not asked separately.)*

**Still not settled, and not decided here:** the exact consent wording and the benefit wording shown in the app. They must come from the owner as
final text, and no claim such as "your data is never shared" is to be written unless it is given.