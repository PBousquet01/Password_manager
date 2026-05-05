# Security TODO

## Client-Side Vault Encryption

Current behavior:
- Website passwords are encrypted in SQLite using Laravel's `encrypted` cast.
- On reveal, Laravel decrypts the website password server-side.
- The plaintext password is sent to the browser only after the logged-in user clicks Reveal.

Future goal:
- Move website password encryption and decryption into the browser.
- Laravel should store only encrypted password payloads.
- Laravel should never receive or decrypt a saved website password in plaintext.

Proposed safe migration plan:
1. Add new database columns for client-side encrypted password data.
2. Keep the current `password` column temporarily for compatibility.
3. Add browser-side key derivation from a vault password.
4. Encrypt website passwords in JavaScript before submitting forms.
5. Store encrypted payload, salt, IV/nonce, and metadata in SQLite.
6. Update detail/reveal/copy behavior to fetch encrypted data and decrypt in the browser.
7. Add backend tests proving plaintext passwords are not returned by Laravel.
8. After the new flow is stable, stop using the old server-decrypted password column.

Affected areas:
- `routes/web.php`
- `resources/views/passwords/create.blade.php`
- `resources/views/passwords/edit.blade.php`
- `resources/views/passwords/show.blade.php`
- `app/Models/StoredPassword.php`
- `database/migrations`
- `tests/Feature/StoredPasswordTest.php`

Notes:
- Do not send the browser decryption key to Laravel.
- Do not store the vault password in SQLite.
- Use HTTPS in real deployments so authentication cookies and reveal requests are protected in transit.
- Browser crypto behavior should eventually be tested with browser-level tests, not only PHP feature tests.
