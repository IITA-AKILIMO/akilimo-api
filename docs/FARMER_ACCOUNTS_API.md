# Farmer accounts: API reference and handover

**IITA Internal Use.** What is built from `docs/FARMER_ACCOUNTS_DESIGN.md` (decisions in its section 8). Interactive docs are in the
OpenAPI (Scramble), tag "Farmer accounts". Status: steps 1 to 5 of the design's section 5 are built and tested; step 6 (the mobile
app) and the owner's inputs (below) are open.

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
| `GET /history`, `GET /history/{request_id}` | farmer token + consent | `read` | only the caller's rows, newest first, `per_page` up to 100; the detail adds `result` |
| `GET /history/ids` | farmer token + consent | `read` | the ids of all saved items, newest first (at most 5000, `meta.truncated` says when cut); an app compares them with its own copies and drops the ones deleted elsewhere |
| `DELETE /history/{request_id}` | farmer token + consent | `write` | |
| `DELETE /history` | farmer token + consent | `sensitive` | deletes **all** the caller's saved advice and keeps the account; answers `deleted` (the count) |
| `DELETE /` | farmer token + consent | `sensitive` | deletes the account; past requests stay with `user_id` and `device_token` cleared |

Sign-in answers use the staff login shape (`token_type`, `token`, `expires_at`, `user`) plus `consent_required` and `claimed_requests`.
A social sign-in that cannot proceed answers with a `code`: `consent_required` (422, first use without consent), `no_email` (422),
`email_unverified` (409, the address belongs to an account and the provider did not vouch for it); a bad or foreign token is 401, and a
provider that is not switched on is 404.

Farmer tokens (name `farmer`) only ever carry `recommendations:compute` and `history:own`, whatever the user's role. Account routes accept
only these tokens (a staff token or an API key gets 403), and history and deletion need recorded consent. Besides the per-IP throttle,
login, register, reset and verify are limited per email address.

`POST /v1/recommendations/compute` stays open to everyone. With a valid farmer token (and consent) the saved row gets `user_id` and the
result is cached apart from the shared cache, and the answer's `request_id` is the id of the saved row (the same id again for a repeat of the
same inputs), so a client can match its copy with `GET /history/{request_id}`; any other credentials, expired tokens included, are treated as
anonymous and get a fresh `request_id` each call. An advice saved
anonymously is claimed once at the next sign-in with the same `device_token` (only rows with no owner move).

**A long history.** The list reads only the columns it shows (never the stored request and answer, which are large) and is paginated (per_page up to 100, newest first, stable order by created_at then id), so a farmer with thousands of saved items costs no more per page than one with a few. A client should not download the history again on every start: fetch newest-first and stop at the first page it already has, and use GET /history/ids now and then to drop items deleted elsewhere (the app does this; see SavedAdviceRepo). Tests: 	ests/Feature/Account/AccountHistoryTest.php (1200 items paged without a loss or repeat, the list query, the ids, delete all).

## Social sign-in (laravel/socialite)

The app signs the farmer in with the provider and sends the provider's OAuth **access token**; the API reads the profile with
`Socialite::driver($provider)->stateless()->userFromToken()`. Socialite does not check which app a token was issued to, so for Google the
API calls Google's `tokeninfo` and requires `aud` or `azp` to be one of `GOOGLE_CLIENT_ID`; Google sign-in is refused while that list is
empty. Add a check per further provider in `SocialSignInService::assertIssuedToUs()` before relying on it. An existing account is linked
only by a **verified** provider email. Identities live in `user_identities` (one row per provider and person).

To add a provider: uncomment its block in `config/services.php` (Apple and Microsoft also need a SocialiteProviders package and driver),
set its variables in `.env`, add it to `FARMER_SOCIAL_PROVIDERS`, and add an audience check.

## Configuration

| Variable | Purpose |
|---|---|
| `FARMER_SOCIAL_PROVIDERS` | providers switched on; **`google` by default** (still refused while `GOOGLE_CLIENT_ID` is empty), e.g. `google,facebook`; a blank value switches social sign-in off |
| `GOOGLE_CLIENT_ID` | non-secret OAuth client IDs, comma separated (Android and web); also the allowed token audiences |
| `<PROVIDER>_CLIENT_ID`, `<PROVIDER>_CLIENT_SECRET`, `<PROVIDER>_REDIRECT_URI` | per provider in `config/services.php`; the redirect URI defaults to `/` and the secret is optional, because the token flow uses neither; secrets never go to the app and are never committed |
| `MAIL_MAILER` | verification and reset email use the Laravel mailer; in production they are only sent once it is not `log` (outside production they are sent) |
| `FARMER_CONSENT_VERSION` | label stored with each consent; the final wording comes from the owner |
| `FARMER_RESET_TTL_MINUTES`, `FARMER_VERIFY_TTL_MINUTES` | code lifetimes (60 and 1440) |
| `FARMER_CHECK_BREACHED_PASSWORDS` | reject breached passwords (calls the Have I Been Pwned range API); off by default |

Verification and reset codes are kept hashed in the cache and work once. Never log a password, code, token or email address.

## Where the code is

| What | Where |
|---|---|
| Endpoints | `AccountController`, `AccountHistoryController`, requests in `app/Http/Requests/Account/`, `FarmerHistoryResource` |
| Logic | `FarmerAccountService`, `SocialSignInService`, `ApiRequestRepo::paginateForUser/findForUser` |
| Middleware | `RequireFarmerToken` (`farmer`), `RequireConsent` (`farmer.consented`), `OptionalFarmerAuth` (`farmer.optional`) |
| Data | migrations `2026_11_12_000021` to `000023`; models `User`, `ApiRequest` (columns added in the child models), `UserIdentity` |
| Abilities | `TokenAbility::HISTORY_OWN`, `TokenAbility::FARMER_ABILITIES` |
| Mail | `AccountCodeMail` and `resources/views/mail/account-code.blade.php` |
| Tests | `tests/Feature/Account/` (auth, social, compute linking, history and deletion); the social tests use a stub Socialite driver and `Http::fake` for Google's tokeninfo, so they need no network |

## Open items

1. **Owner inputs (design section 8):** the hosted email service and a verified sending domain (then set `MAIL_MAILER`); the Google Cloud
   OAuth client IDs for `GOOGLE_CLIENT_ID` (Google sign-in is already on by default and starts working once it is set); the final consent and benefit wording
   (then `FARMER_CONSENT_VERSION` and the app text). No claim such as "your data is never shared" is to be written unless it is given.
2. **Mobile app (`akilimo-mobile`, step 6): built, not yet run on a device.** Sign-up, sign-in, consent, reset password, email
   confirmation, saved-advice list (offline from Room), share, sign-out and account deletion; Google through Play Services
   `AuthorizationClient` (an **access token**); the token encrypted with an Android Keystore key. Still open there: try it on a phone against
   a server with the account routes, the Google Cloud OAuth client (the Google button is **on by default** in the app; `GOOGLE_SIGN_IN=false` hides it, and until the client exists it shows but fails with a message),
   the final consent text (the app has a draft in `strings_account.xml`), and the sw, rw and ha translations of the new strings.
3. **Other providers:** an audience check for each one before it is switched on.
4. **Later:** move the playground history from `device_token = 'playground-<id>'` to `user_id`; keep `user_id` and `device_token` out of
   any public export of `api_requests` (the rows kept after an account deletion still hold the farm location and answers).
