# Farmer accounts: API reference

**IITA Internal Use.** What is built from `docs/FARMER_ACCOUNTS_DESIGN.md` (decisions in its section 8). Interactive docs are in the OpenAPI (Scramble), tag "Farmer accounts".

## Endpoints (`/api/v1/account`)

| Method and path | Auth | Throttle | Notes |
|---|---|---|---|
| `POST /register` | none | `sensitive` | `email`, `password` (8+), optional `name`, `consent: true` (required), optional `device_token` |
| `POST /login` | none | `auth` | `email`, `password`, optional `device_token`. `consent_required: true` in the answer for an account with no consent yet |
| `POST /google` | none | `auth` | `access_token` (Google OAuth), `consent` on first use, optional `device_token` |
| `POST /social/{provider}` | none | `auth` | same body, any provider listed in `FARMER_SOCIAL_PROVIDERS` |
| `POST /consent` | farmer token | `write` | `consent: true`, optional `device_token`; claims the phone's earlier advice |
| `POST /logout` | farmer token | `write` | revokes the current token |
| `POST /email/verify` | none | `sensitive` | `email`, `code` (mailed) |
| `POST /email/resend` | farmer token | `sensitive` | 503 while email is off |
| `POST /password/forgot`, `POST /password/reset` | none | `sensitive` | same answer for known and unknown addresses; a reset signs the account out everywhere |
| `GET /history`, `GET /history/{request_id}` | farmer token + consent | `read` | only the caller's rows; the detail adds `result` |
| `DELETE /history/{request_id}` | farmer token + consent | `write` | |
| `DELETE /` | farmer token + consent | `sensitive` | deletes the account; past requests stay with `user_id` and `device_token` cleared |

Sign-in answers use the staff login shape (`token_type`, `token`, `expires_at`, `user`) plus `consent_required` and `claimed_requests`.
Farmer tokens (name `farmer`) only ever carry `recommendations:compute` and `history:own`, whatever the user's role.
Besides the per-IP throttle, login, register, reset and verify are limited per email address.

`POST /v1/recommendations/compute` stays open to everyone. With a valid farmer token (and consent) the saved row gets `user_id`;
any other credentials, expired tokens included, are treated as anonymous. An advice saved anonymously is claimed once at the next
sign-in with the same `device_token` (rows with no owner only).

## Social sign-in (laravel/socialite)

The app signs the farmer in with the provider and sends the provider's OAuth **access token**; the API reads the profile with
`Socialite::driver($provider)->stateless()->userFromToken()`. Socialite does not check which app a token was issued to, so for
Google the API calls Google's `tokeninfo` and requires `aud`/`azp` to be one of `GOOGLE_CLIENT_ID`. Add a check per further provider.
An existing account is linked only by a **verified** provider email. Identities live in `user_identities` (one row per provider and person).

## Configuration

| Variable | Purpose |
|---|---|
| `FARMER_SOCIAL_PROVIDERS` | providers switched on, e.g. `google,facebook`; empty keeps social sign-in off |
| `GOOGLE_CLIENT_ID` | non-secret OAuth client IDs, comma separated (Android and web); no secret is needed or stored |
| `FARMER_MAIL_ENABLED` | force verification and reset email on or off; unset means on only with a real mailer or outside production |
| `MAIL_MAILER` | stays `log` until the hosted email service (decision 3) is set up |
| `FARMER_CONSENT_VERSION` | label stored with each consent; the final wording comes from the owner |
| `FARMER_CHECK_BREACHED_PASSWORDS` | reject breached passwords (calls the Have I Been Pwned range API) |

Verification and reset codes are kept hashed in the cache (one use, 24 h and 60 min).
