# Appointment clock display fix

Prepared on `codex/appointment-time-display` from `origin/basicbranch1` at `436a8f0d1f3814e9ef1ad9f90a1ca843c989e59a`. Live deployment is unverified. Its fixtures contain no patient data.

**Problem and change**

The Appointments list and Patient Appointment History treated stored local `confirmed_time` values as Unix timestamps using `strtotime()`, then converted them with `wp_date()`. With PHP interpreting the input as UTC and the site displaying IST, a synthetic `09:15:00` appeared as `2:45 PM`.

Each plugin now parses `HH:mm` or `HH:mm:ss` explicitly in the site timezone, checks the clock value, and formats the resulting timestamp in that same timezone. The existing HTML escaping and WordPress localization remain in place. Stored appointment values are preserved; this patch only changes confirmed-time presentation and adds its formatting helpers.

The helpers have distinct names so either plugin can load independently. Plugin/database versions and save handlers are unchanged; no migration is required.

**Validation**

Run `php tests/appointment-time-display.php` from the repository root. The test loads both complete plugin modules with inert hook stubs and tests the actual helper functions. It does not bootstrap WordPress or connect to a database.

Native PHP was unavailable on the MacBook. Verification used PHP 8.3.33 from official WordPress PHP WASM packages `@php-wasm/universal@3.1.58` and `@php-wasm/node-8-3@3.1.58`, installed in an isolated temporary test directory.

```text
PHP 8.3.33
PASS: both full plugin modules parse; both displays use their clock helpers.
PASS: the old formatter reproduces the +330-minute regression.
PASS: 264 clock checks across two helpers, three site timezones and two PHP timezones.
No WordPress bootstrap, patient data, database, or provider calls were used.
```

Tests cover midnight, noon, end-of-day, minute/second formats, empty/invalid values, named/fixed-offset site timezones and PHP timezone independence. An independent diff review found no material issue. `git diff --check` passes. Live WordPress rendering still requires read-back after deployment.

**Deployment verification and separate issues**

After applying the two plugin changes, reload an existing appointment detail, the Appointments list and its Patient Appointment History. Confirm the same saved date/time, reference, doctor, status and patient link; the two list/history clock displays should match the stored local time. Verify using existing records without changing their values.

This bounded patch covers the two `confirmed_time` display sites. Created/Last Updated full-datetime labels have analogous conversions that need a separate review. The appointment save validator still accepts only `HH:mm`; resubmitting a stored `HH:mm:ss` value can clear it. Until that separate write-path issue is addressed, refill the intended same `HH:mm` value before saving other appointment changes.

**Sources**

- [WordPress wp_date: Unix timestamps and the site output timezone](https://developer.wordpress.org/reference/functions/wp_date/)
- [WordPress wp_timezone](https://developer.wordpress.org/reference/functions/wp_timezone/)
- [Official WordPress PHP WASM packages](https://developer.wordpress.org/playground/developers/architecture/php-wasm-packages/)

Sutradhara — Codex on MacBook
