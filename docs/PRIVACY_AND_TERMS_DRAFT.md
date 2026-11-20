# AKILIMO API: privacy policy and terms of use, draft wording for accounts, saved advice and PDF reports

**IITA Internal Use.** Draft for the owner. Not legal text and not approved for external sharing. Written 2026-11-20 from the code
(`FarmerAccountService`, `SocialSignInService`, `RecommendationReportService`, `ApiRequestRepo`, `AkilimoComputeData`,
`config/farmer-accounts.php`, `config/akilimo-compute.php`).

## 0. Where the policy lives

This repository holds **no** privacy policy or terms text (only the design notes in `docs/FARMER_ACCOUNTS_DESIGN.md`).
The published policy is a separate web page: <https://akilimo.org/akilimo-privacy-policy.html> ("Privacy Policy - AKILIMO Mobile
App", last updated 12 June 2025, provider IITA, contact akilimo@cgiar.org). That page predates farmer accounts. It says nothing
about accounts, sign-in, saved history, PDF reports or emailing them, so sections 1 to 5 below are the **proposed additions** to
it. There are no separate terms of use in the repo or on that page.

**Consent version:** the wording below is `FARMER_CONSENT_VERSION=draft-1`. When the owner approves or changes any wording that
farmers agree to, **the owner must bump `FARMER_CONSENT_VERSION`** (for example `v1`), so each stored consent records which text was
accepted. Accounts that agreed to an older label are not asked again by the code; the owner must decide whether re-consent is needed.

## 1. Optional accounts (proposed text)

- An AKILIMO account is optional. The app works without one.
- You can sign up with an email address and password, or sign in with Google (other providers can be switched on by IITA).
- We store: your email address, your name (optional; for Google, the name from your Google profile), a hashed password (none for
  Google-only accounts), whether your email is confirmed, the link between your account and your sign-in provider (provider name and
  your ID at that provider), and the version and time of your consent.
- Confirmation and password-reset codes are sent to your email, and expire (`FARMER_VERIFY_TTL_MINUTES`, default 1440;
  `FARMER_RESET_TTL_MINUTES`, default 60). Only a hash of each code is kept, in a cache.
- Your consent is recorded when you sign up or first sign in. Until you consent, your advice is not linked to your account and no
  history is shown.
- Account sign-in lasts `AUTH_TOKEN_TTL_DAYS` (default 30) days; resetting a password signs you out everywhere.

## 2. Saved advice history (proposed text)

- When you are signed in and have consented, each advice request is linked to your account. A saved request holds your farm
  location, your answers and the result.
- **Earlier advice from your phone:** the app gives each phone a random `device_token`. When you sign in after consenting, advice
  saved by that phone that no account owns yet is linked to your account. Advice already owned by another account is never taken.
- You can delete one item, several, or all of your saved advice, and you can delete your account. These are **soft deletes**: the
  request is kept as an **anonymous statistic**, with the link to you removed (`user_id` and `device_token` are cleared). Location
  and answers stay in that record.
- Deleting your account also removes your sign-in tokens, API keys, linked sign-in identities and the account itself, with the
  same effect on your saved advice.
- IITA keeps saved advice until you delete it; there is no scheduled purge (owner decision, 2026-11-12).

## 3. PDF advice reports (proposed text)

- Each recommendation can come with PDF reports (fertilizer, intercropping, planting practices, planting dates).
- The PDF contains your **name, a masked phone number and your farm location**.
- PDFs are stored on a private disk (never a public one) and are not publicly listed. You receive a **signed download link that
  expires** after `AKILIMO_REPORTS_LINK_TTL_DAYS` days (default 7). When signed in you can ask for a **new link** for your own saved
  advice at any time (`GET /v1/account/history/{requestId}/report`). IITA staff with admin access to the request log can also make links.
- PDFs are kept as long as the saved history, unless IITA sets `AKILIMO_REPORTS_KEEP_DAYS` (default: no limit), after which they are deleted.
- If you give an email address, the PDF can be **emailed to that address** (`AKILIMO_REPORT_EMAIL_ENABLED`, default on; in production only once a real mailer is configured, see `emailEnabled()`). Email is not a secure channel; anyone with access to
  that inbox or the link can open the report until the link expires.

> **Gap to decide (found in the code):** deleting saved advice or an account only clears `user_id` and `device_token`. It does **not**
> delete the stored PDF, which still holds the farmer's name, masked phone number and location, and the request record keeps
> them as well. The text above does not promise that PDFs are deleted. Before the owner states that "deleted advice is anonymous",
> either decide to delete the PDFs on deletion (`RecommendationReportService::forget()` exists but is not called for this) or say
> plainly that the data in the kept record is not removed.

## 4. Data sent to the Akilimo Compute service (proposed text)

To produce advice the API sends the request to the Akilimo Compute service (`AKILIMO_COMPUTE_BASE_URL`), which is operated as part
of the AKILIMO system. The request contains: farm location (latitude, longitude), country, field size and unit, planting and
harvest dates, crop, yield and price information, costs, fertilizer choices and prices, language, **your name, phone country code
and number, your email address if given, and your farm name** (they appear on the PDF), and the API's request ID. It does not send
your password, account ID, sign-in provider data or `device_token`. The compute service returns the advice and the PDFs. The API
logs requests and responses when `AKILIMO_COMPUTE_LOGGING` is on (default on; the code redacts user fields in these logs).

## 5. Links to the existing policy

The existing page also covers: device information, approximate location, sharing of aggregated anonymised data with Google Play
Services, Google Analytics for Firebase and Sentry, children under 13, security, and policy changes. Keep those; the
statement there that data is kept "a reasonable time" after use should be reconciled with section 2 (kept until deleted).

## Open TODOs for the owner (none of these are decided here)

1. **TODO final wording** of the consent text and benefit text shown in the app; then bump `FARMER_CONSENT_VERSION`.
2. **TODO controller name:** the existing page names IITA as service provider but not as data controller. Confirm the legal entity.
3. **TODO contact details:** the page gives only akilimo@cgiar.org, no postal address or data protection officer. Confirm.
4. **TODO jurisdiction and governing law:** not stated anywhere. Needed for terms of use and user rights (access, correction, complaint).
5. **TODO terms of use:** none exist. Decide whether they are wanted and write them (acceptable use, API keys, disclaimer of advice accuracy).
6. **TODO PDF deletion:** decide the gap in section 3 (delete PDFs on delete, or disclose).
7. **TODO retention:** confirm "until the farmer deletes it" for history, and set `AKILIMO_REPORTS_KEEP_DAYS` if a limit is wanted. The anonymous statistics are kept with no limit.
8. **TODO third parties:** confirm the compute service host/operator, the hosted email provider (not yet chosen) and Google as sign-in provider are named in the policy.
9. **TODO re-consent:** decide whether existing accounts must consent again after a wording change.
10. **TODO policy page:** the akilimo.org page is outside this repo; someone must update it, and its date.
