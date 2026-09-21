# BIMBoleh Hosting Checklist

Use this checklist for the final domain deployment. The application is designed for PHP 8.0+, MySQL/MariaDB, Apache or LiteSpeed, and HTTPS.

## 1. Prepare the database

1. Create a database in the hosting control panel.
2. Create a dedicated database user with a strong unique password. Do not use `root`.
3. Grant that user access only to the BIMBoleh database.
4. In phpMyAdmin, select the new database before importing SQL.
   - Fresh installation: import `database/schema.sql`.
   - Existing installation: import `database/migrations/001_add_quiz_type.sql`, then `database/migrations/002_add_login_throttling.sql`.

The runtime account needs `SELECT`, `INSERT`, `UPDATE`, and `DELETE`. Schema imports additionally require temporary `CREATE`, `ALTER`, and `INDEX` permission.

## 2. Configure production secrets

Copy `includes/local_config.example.php` to `includes/local_config.php` on the server and replace every placeholder. The copied file is ignored by Git.

Required production values:

```php
'BIM_APP_ENV' => 'production',
'BIM_DB_HOST' => 'localhost',
'BIM_DB_PORT' => '3306',
'BIM_DB_NAME' => 'hosting_database_name',
'BIM_DB_USER' => 'hosting_database_user',
'BIM_DB_PASS' => 'strong_unique_password',
```

Set file permissions so the hosting account can read `local_config.php` but visitors cannot. A typical target is `640`, where supported. Never upload this secret file to GitHub.

Environment variables with the same names may be used instead. Environment variables take priority over `local_config.php`. `BIM_APP_ENV` accepts only `local`, `production`, or `testing`; an unknown value stops the application instead of falling back. Local mode also refuses non-local hostnames so a forgotten production configuration cannot silently go live.

## 3. Upload safely

Deploy the project at the domain document root. Keep the included `.htaccess` file: it disables directory listing and blocks web access to internal code, SQL, locale source files, Git metadata, logs, and secret configuration.

After upload, confirm these requests return `403` or `404`:

- `/database/schema.sql`
- `/includes/local_config.example.php`
- `/locales/en.php`
- `/.git/HEAD`

If the host uses Nginx and ignores `.htaccess`, ask the host to deny the equivalent paths before launch.

`test_db.php` is automatically unavailable in production. Do not enable diagnostics on the public site.

## 4. Connect the domain and HTTPS

1. Point the domain DNS records to the hosting provider.
2. Issue and enable an SSL/TLS certificate.
3. Enable the provider's permanent HTTP-to-HTTPS redirect.
4. Open the final HTTPS address and confirm the browser shows a secure connection.

Camera access requires HTTPS on a public domain. BIMBoleh sends Secure session cookies and HSTS only after PHP detects HTTPS. Set `BIM_TRUST_PROXY` to `1` only if a trusted provider such as the host or configured reverse proxy terminates HTTPS and sends `X-Forwarded-Proto`. Do not enable it for arbitrary proxy traffic.

## 5. Production smoke test

Run these checks using the final HTTPS domain:

1. Register a new test account with a valid email and 8–128 character password.
2. Log in, refresh, and confirm the session remains active.
3. Log out using the header button and confirm protected pages return to login.
4. Verify learning cards open the correct BIM Sign Bank references.
5. Complete alphabet and number theory quizzes; confirm dashboard history and leaderboard results.
6. Test both camera pages, permission denial, Stop, and page exit.
7. Switch through Malay, English, Simplified Chinese, and Tamil.
8. Test mobile layout, extra-large text, high contrast, and reduced motion.
9. Confirm PHP errors are not displayed to visitors.

## 6. Operations after launch

- Back up the database before every migration and at least weekly.
- Keep the hosting PHP version and TLS certificate current.
- Review PHP/server error logs without exposing them publicly.
- Use unique hosting, database, and GitHub passwords.
- Do not turn the temporary camera challenge into formal grading or a public ranking without a different trusted verification design.
- Test updates on a branch and merge through a pull request before redeploying.
