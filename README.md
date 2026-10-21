# Bågspår

[Bågspår](https://bagspar.se) is a site for window-sash renovation workshops in Sweden. The product is a work card per sash: marking, photos, notes, and work steps, including when mobile coverage is poor.

Live site: [https://bagspar.se](https://bagspar.se). Operated by Prodi Digital AB.

## Who it is for

Workshops that take wooden sashes out, restore them, and put each sash back in the same opening. The site explains that workflow, shows one fictional example card, and lets a workshop create an account.

## Trial, price, and invoicing

Numbers live in `config/bagspar.php` and every public sentence about them is built by `App\Support\Pricing`:

- 30-day free trial from registration (`trial_days`). No card.
- `/registrera` uses the same Cloudflare Turnstile check as the contact form (skipped when either `TURNSTILE_*` key is empty). When a new owner confirms the email address, `LEAD_NOTIFICATION_EMAIL` gets a short “Nytt verkstadskonto” mail, once per workshop (`registration_notified_at`). Unverified signups send nothing.
- 590 kr/month excluding VAT per workshop, 3 users included, 149 kr/month per extra user. 5 900 kr/year when paid annually.
- The owner orders under `/app/abonnemang`, choosing monthly or yearly, and gives the organization number and an invoice address. The order mails `LEAD_NOTIFICATION_EMAIL` with everything needed for the invoice and sends the workshop a confirmation. Invoices are issued by hand (Fortnox); there is no card payment and no Stripe.
- Access is the latest of `trial_ends_at`, `paid_until`, and `subscription_ordered_at` plus `order_grace_days` (30). When it has passed, the `workshop.access` middleware sends every `/app` request to the billing page. Exports stay open so no data is held hostage. The admin records `paid_until` under `/admin/verkstader` once an invoice is sent or paid.
- A daily command (`bagspar:send-onboarding-nudges`, 09:00) mails the owners of a trial workshop that still has no project five days after registration, once per workshop. Access-ending reminders (`bagspar:send-access-reminders`, 08:00) go out three days before the trial or paid period ends.
- Workshops created before this change keep the `trial_ends_at` they were given.

## What this repository ships

This repository is the public site and the example card.

| Path | Purpose |
| --- | --- |
| `/` | Homepage, price, and contact form |
| `/demo` | Example work card for fictional sash 2-KÖK-F1-B2 |
| `GET` and `POST /demo/synk` | Reads and stores that example’s step and notes |
| `/fonsterrenovering-dokumentation` | Guide: what to record on each sash |
| `/arbetskort-fonsterbagar` | Guide: one work card per sash |
| `/markning-av-fonsterbagar` | Guide: marking so a sash returns to the same opening |
| `/integritetspolicy` | Privacy policy. `/integritet` redirects here (301) |
| `/sitemap.xml` | Public HTML pages. Locations use `APP_URL` |
| `/robots.txt` | Static file. Points crawlers at the sitemap |
| `/manifest.webmanifest` | Web app manifest. Start URL is `/demo` |
| `POST /intresse` | Saves an interest signup and sends mail when configured |
| `/admin/anmalningar` | Signup list. `/admin/anmalningar.csv` downloads the same rows |
| `/admin/verkstader` | Workshop list with trial, order, and paid period. `PATCH /admin/verkstader/{id}` records `paid_until` and the plan |
| `POST /app/projekt/{id}/statuslank/skicka` | Mails the public status link to the project's customer from the project page. Needs the link switched on and a customer e-mail |
| `/app/projekt/{id}/offert` and `/offert/{token}` | The workshop writes, sends, and downloads a quote. The customer opens the public link and accepts with a name |
| `/app/projekt/{id}/mottagning`, `/app/projekt/{id}/mottagning.pdf`, `POST /app/projekt/{id}/mottagning/godkann` and `DELETE /app/projekt/{id}/mottagning/godkannande` | The intake protocol: sash condition and the before photos, as a page and a PDF. The workshop records an on-site approval; the owner can remove an approval |
| `POST /status/{token}/mottagning/godkann` | The customer approves the intake protocol on the status link with a name. Stored once |
| `/app/konto` | Every user changes their own name, e-mail, and password. A new e-mail must be confirmed again; a new password ends the user's other sessions |
| `/app/abonnemang` | Billing page inside the workshop app. The owner orders or changes the subscription |
| `/up` | Laravel health check |

The example card lives in the browser (`localStorage`). Changing the step or notes posts them to `/demo/synk`, which keeps them in cache for 14 days (`config('bagspar.demo_sync_days')`). Photos added on the example stay in the browser and are not uploaded. A service worker (`public/sw.js`) caches `/demo` and its assets so the example can be opened offline. `demo/synk` is exempt from CSRF because a later visit may not have a fresh session token.

`POST /intresse` accepts workshop name (`company`) and email, rejects a `website` honeypot field, and is limited to 5 requests per minute. The same email does not create a second lead. The signup stores `consented_at` as the time the form was sent. The visitor is redirected back to the homepage. When `LEAD_NOTIFICATION_EMAIL` is set, that address receives a message with the subject “Ny anmälan för Bågspår”. The workshop also receives a Swedish confirmation, sent synchronously. A mail failure is logged; the signup is still saved.

`/admin/anmalningar` returns 404 when `BAGSPAR_ADMIN_TOKEN` is empty. Otherwise it accepts that value as an HTTP Basic password (any username) or as an `Authorization: Bearer` token. Rows are listed newest first, in Europe/Stockholm time. The CSV columns are Företag, E-post, and Datum.

## Stack

- PHP 8.4.1 or newer (`composer.json` allows `^8.3`; the locked Symfony packages require `>=8.4.1`)
- Laravel ^13.17 (`laravel/framework` v13.35.0 in `composer.lock`)
- PHPUnit ^12.5 and Laravel Pint
- SQLite by default (`DB_CONNECTION=sqlite`)
- Public pages: Blade, `public/css/site.css`, `public/js/site.js`, and `public/js/demo.js`
- Vite ^8 and Tailwind CSS ^4 (`npm run build` compiles `resources/css/app.css` and `resources/js/app.js`; the public pages do not load that bundle)
- Postmark via `symfony/postmark-mailer` when `MAIL_MAILER=postmark`

Production sits behind Cloudflare. The app trusts the forwarded scheme and client IP, and forces HTTPS URLs when `APP_ENV=production`. A production request that still arrives as HTTP is redirected to HTTPS. Requests that already have `X-Forwarded-Proto: https`, and requests to localhost, are not redirected. Port 80 in front of Cloudflare is answered by Cloudflare, not by this app. Forge’s Nginx is what listens on the origin.

## Local setup

PHP 8.4.1 or newer, Composer, and Node.js.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan serve
```

`composer run dev` runs `php artisan dev`. `.env.example` sets `APP_URL` to `http://bagspar.test`. Point `APP_URL` at the URL you actually open.

## Environment

Copy `.env.example`. Keep real values in `.env` and on the server. Do not commit API keys.

| Variable | Used for |
| --- | --- |
| `APP_URL` | Sitemap and canonical URLs. Production rewrites an `http` value to `https`. The sitemap falls back to `https://bagspar.se` when the value is not a usable URL |
| `APP_KEY` | Application key from `php artisan key:generate` |
| `LEAD_NOTIFICATION_EMAIL` | Recipient of new signups, verified workshop accounts, contact messages, and subscription orders. Mail is skipped when this is empty. See `config/bagspar.php` |
| `POSTMARK_API_KEY` | Postmark server token (`config/services.php`). Leave it empty in `.env.example` |
| `MAIL_MAILER` | `postmark` on the server. Unset, Laravel uses `log` |
| `MAIL_FROM_ADDRESS` | From address. `.env.example` uses `noreply@bagspar.se` |
| `MAIL_FROM_NAME` | From name. Defaults to `APP_NAME` |
| `BAGSPAR_ADMIN_TOKEN` | Secret for the signup list. Empty hides those routes with 404 |
| `BAGSPAR_CONTACT_EMAIL` | Footer and privacy-policy contact. Empty uses `kontakt@bagspar.se`. That domain had no MX record when this was written, so the mailbox has to exist or the variable has to point at an address that already receives mail |
| `GA_MEASUREMENT_ID` | Optional Google Analytics 4 measurement ID. Empty uses the public ID `G-SSL2JZK3LY`. `gtag.js` is requested only after the visitor chooses Godkänn |

## Tests and build

Run these before pushing to `master`:

```bash
php artisan test
npm run build
vendor/bin/pint --dirty
```

`php artisan test` uses an in-memory SQLite database and reads `APP_KEY` from `.env`. Pint formats PHP; run it when PHP files changed.

## Deploy

[Laravel Forge](https://forge.laravel.com/) deploys this app on every push to `master`.

1. Run `php artisan test` and `npm run build`.
2. Run Pint when PHP files changed.
3. Push to `master` only if those commands succeed.

If the live site fails after that deploy, revert the commit on `master`.

A production `composer install` runs `@php scripts/cache-config.php`. The command has no shell variables. The script rebuilds the config cache when `APP_ENV=production` or the deploy is on Forge, so `GA_MEASUREMENT_ID` and its public default are picked up.

On the server, set `APP_URL` to `https://bagspar.se`, `MAIL_MAILER=postmark`, `POSTMARK_API_KEY`, `MAIL_FROM_ADDRESS`, `LEAD_NOTIFICATION_EMAIL`, and `BAGSPAR_ADMIN_TOKEN`. Leave the API key and the admin token out of git.
