# Summit Krayin CRM — Roadmap

**Project:** Fork of Krayin CRM (Webkul) for Summit Maryland, an ASAM 3.1/3.5
SUD treatment provider. Goal: referral-network lead management to fill beds,
with alumni/patient outreach and HIPAA compliance.

**Repo:** `https://github.com/andrewotis/summit-kraylin`
**Hosting:** AWS Lightsail + RDS (MySQL) — `https://crm.summitmaryland.org`
**Stack:** Laravel 12, PHP 8.3, MySQL 8.0+, Vue.js

---

## ✅ Already in place

- **BAA with Amazon (AWS)** — executed
- **RDS encryption at rest** — enabled (AES-256 via KMS)
- **SSL/TLS** — Certbot + Let's Encrypt, auto-renewal, HTTP→HTTPS redirect at
  server level (ForceTls middleware is redundant but harmless)
- **Separate dev AWS account** — Tom has root (billing only), Andrew has a
  dedicated developer IAM account

---

## Customizations (vs. upstream Krayin 2.2)

### Security & HIPAA hardening (custom middleware)

| Middleware | File | Purpose |
|---|---|---|
| `ForceTls` | `Admin/src/Http/Middleware/ForceTls.php` | Redirects HTTP→HTTPS (excluding local/testing) |
| `SecurityHeaders` | `Admin/src/Http/Middleware/SecurityHeaders.php` | CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy |
| `RequiresTwoFactor` | `Admin/src/Http/Middleware/RequiresTwoFactor.php` | Enforces TOTP challenge after login if 2FA is configured |
| `MaxSessionLifetime` | `Admin/src/Http/Middleware/MaxSessionLifetime.php` | Hard 8-hour (28800s) absolute session expiration |
| `IdleTimeout` | `Admin/src/Http/Middleware/IdleTimeout.php` | 15-minute (900s) idle timeout, logs out automatically |

### Two-factor authentication (TOTP)

- `TwoFactorController` — Google2FA-based TOTP setup, enable, verify, disable
- Recovery codes (10 codes, consumed on use)
- Emergency access workflow: admins grant time-limited (1 hour) recovery tokens
- Routes guarded by `two-factor` middleware, with bypass for challenge flow

### Audit logging

- `Core\Traits\Auditable` trait — logs `create`, `update`, `delete` to `audit_logs` table
- Encrypted attributes are redacted as `[ENCRYPTED]` in audit logs
- Applied to: `Person`, `Activity`, `Lead`, `Email`, `Quote`, `Organization` models

### Data-at-rest encryption (Laravel encrypted casting)

| Model | Encrypted fields |
|---|---|
| `Person` | `name`, `emails`, `contact_numbers` |
| `Organization` | `name`, `address` |
| `Lead` | `title`, `description` |
| `Activity` | `title`, `location`, `comment`, `additional` |
| `Email` | `subject`, `name`, `reply`, `sender`, `from`, `reply_to`, `cc`, `bcc`, `reference_ids` |
| `Quote` | `subject`, `description`, `billing_address`, `shipping_address` |
| `User` | `google2fa_secret`, `two_factor_recovery_codes`, `emergency_token` |
| `AttributeValue` | `text_value`, `json_value` |

### Custom packages (new)

1. **MailingList** (`packages/Webkul/MailingList/`)
   - `mailing_lists` table — named groups for targeted email
   - `subscribers` table — links a `Person` to a `MailingList` with subscription status
   - Migrations: June 8–9, 2026

2. **Marketing** (`packages/Webkul/Marketing/`)
   - `marketing_events` — scheduled dates for campaign dispatch
   - `marketing_campaigns` — ties an email template + event + optional mailing list
   - `CampaignMail` mailable — renders templated emails, replaces placeholders
   - `Campaign` helper — Artisan command `marketing:campaigns-process` dispatches queued emails
   - **Key feature:** if `mailing_list_id` is set on a campaign, only subscribers of that list (with `is_subscribed = true`) receive emails; otherwise falls back to all persons

3. **CampaignManager** (`packages/Webkul/CampaignManager/`)
   - Adds `mailing_list_id` FK to `marketing_campaigns`
   - Bridge between Marketing and MailingList packages

### Core code modifications

- **LeadController:** Lead create/edit now shows user-defined custom attributes in addition to core attributes
- **PersonController::search:** Now searches by `emails` and `contact_numbers` in addition to `name` (for lead creation lookup)
- **Activity model:** Added `Auditable` trait + encrypted casts for `title`, `location`, `comment`, `additional`
- **ACL & Menu:** Added `mailing_lists`, `subscribers`, `campaigns`, `emergency_access` entries
- **Branding:** Login/password reset/2FA challenge views branded "Summit"
- **Mailer:** SES configured; `MAIL_FROM_ADDRESS=outreach@summitmaryland.org`

### Infrastructure

- Mail driver: SES (AWS Simple Email Service) via `ses` transport
- Queue: `sync` (development) — production needs Redis + `redis` driver + worker
- Cache: `file` — production should use Redis
- Session: `file` — production should use Redis
- HTTPS: Certbot + Let's Encrypt at the Nginx/Apache level (server-level redirect)

---

## HIPAA Compliance Analysis

### ✅ What's done well

| Requirement | Status | Evidence |
|---|---|---|
| BAA with AWS | ✅ | Executed |
| Encryption at rest (RDS) | ✅ | RDS KMS encryption enabled |
| Encryption at rest (app-level) | ✅ | Laravel encrypted casting on all PHI models |
| Encryption in transit | ✅ | Certbot SSL, HSTS header, ForceTls middleware, SecurityHeaders |
| Access control (RBAC) | ✅ | Krayin ACL with roles/permissions; custom ACL entries for mailing lists/campaigns |
| Authentication (MFA) | ✅ | Google2FA TOTP, recovery codes, emergency access |
| Audit logging | ✅ | `Auditable` trait on all PHI-touching models; encrypted values redacted |
| Session management | ✅ | Hard 8-hour max lifetime, 15-min idle timeout |
| CSP / security headers | ✅ | CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy |
| Input sanitization | ✅ | SVG sanitizer (`enshrined/svg-sanitize`), XSS fixes applied upstream |

### ❌ Gaps (needs remediation)

| Gap | Severity | Details |
|---|---|---|
| **Queue = sync** | **High** | Campaign emails send synchronously — blocks the request. Must switch to Redis + worker for async dispatch |
| **Session driver = file** | **High** | File-based sessions not encrypted at rest. Move to Redis with `phpredis` |
| **Cache driver = file** | **Medium** | File cache is fine for single-server but Redis is preferred |
| **Logging** | **Medium** | Default `stack`→`single` channel writes plaintext. Needs CloudWatch with KMS encryption + retention policy (min 6 years) |
| **CORS wildcard** | **Medium** | `allowed_origins` = `['*']` in `config/cors.php` — lock to `crm.summitmaryland.org` |
| **SES TLS verify** | **Low** | SMTP config has `'verify_peer' => false` — should be `true` in production |
| **`APP_DEBUG=true`** | **Medium** | `.env` has debug on — must be `false` in production |
| **No PHI access log review** | **Medium** | Audit log exists but no process for regular review/alerting |
| **Unsubscribe not implemented** | **High** | `CampaignMail` strips `{%unsubscribe_url%}` to empty string — required for CAN-SPAM |
| **No PHI data classification** | **Low** | No documented data classification policy mapped to DB columns |
| **IMAP credentials** | **Medium** | Need to store creds in AWS Secrets Manager, not `.env` |
| **Sanctum token expiration** | **Low** | `expiration => null` — tokens never expire by default |

---

## Roadmap Phases

### Phase 1: Redis & Queue Infrastructure (Immediate — 1 week)

- [ ] Install Redis on Lightsail instance
- [ ] Configure Redis: `bind 127.0.0.1`, set `requirepass` (strong password), enable RDB/AOF persistence
- [ ] Install `phpredis` extension or add `predis/predis` to composer
- [ ] Update `.env`: `REDIS_HOST=127.0.0.1`, `REDIS_PASSWORD=<your-password>`, `REDIS_PORT=6379`
- [ ] Switch session: `SESSION_DRIVER=redis`, `SESSION_CONNECTION=session`
- [ ] Switch cache: `CACHE_DRIVER=redis`
- [ ] Switch queue: `QUEUE_CONNECTION=redis`
- [ ] Install supervisor, configure queue worker daemon (`php artisan queue:work redis --sleep=3 --tries=3`)
- [ ] Set `APP_DEBUG=false` in production `.env`
- [ ] Lock CORS to `crm.summitmaryland.org`
- [ ] Enable `verify_peer => true` in SES mail config

### Phase 2: One-Click Unsubscribes (1–2 weeks)

- [ ] Generate secure signed unsubscribe URL in `CampaignMail` (HMAC-signed token with person_id + mailing_list_id)
- [ ] Create unsubscribe route/controller that validates the token and sets `is_subscribed = false`
- [ ] Update `CampaignMail` to render proper `{%unsubscribe_url%}` instead of stripping it
- [ ] Add "Unsubscribe from all" option
- [ ] Add opt-out confirmation page (no login required)
- [ ] Add compliance notice to email footer (CAN-SPAM required language)

### Phase 3: Bounce & Bad-Email Management (2–3 weeks)

- [ ] Set up SES SNS notifications for bounces, complaints, and delivery notifications
- [ ] Create SNS endpoint/webhook in Laravel to receive bounce/complaint callbacks
- [ ] On bounce: increment bounce counter on subscriber/person record
- [ ] After N bounces (e.g., 3): auto-deactivate subscriber set `is_subscribed = false`, flag person email as invalid
- [ ] On complaint: immediately set `is_subscribed = false`, add to suppression list
- [ ] Build admin UI to view bounce/complaint history
- [ ] Build admin UI to manually re-activate or clean addresses

### Phase 4: Contact & Lead Exporting (1–2 weeks)

- [ ] Add CSV/Excel export buttons to Persons, Leads, and Organizations datagrids
- [ ] Export respects ACL — users only export what they can see
- [ ] Encrypted fields decrypt on export for authorized users (audit-log the export action)
- [ ] Support filtered exports (export current search/view, not just entire table)

### Phase 5: Campaign Metrics & Performance Dashboard (3–4 weeks)

- [ ] Track email delivery status per recipient (sent, delivered, bounced, opened, clicked)
- [ ] Build campaign metrics model + migration (campaign_id, sent_count, delivered_count, open_count, click_count, bounce_count, complaint_count)
- [ ] Implement open tracking (1x1 transparent pixel with unique campaign+person token)
- [ ] Implement click tracking (rewrite links through app to log clicks)
- [ ] Build campaign performance dashboard with charts (total sent, open rate, click rate, delivery success)
- [ ] Add per-campaign detail view with recipient-level status

### Phase 6: Tag-Based Email Targeting & Organization Tags (2–3 weeks)

- [ ] **Organization tags:** Create `organization_tags` pivot table + migration + `tags()` relationship on `Organization` model
- [ ] **Organization tag controller/routes:** Add `TagController` for organizations (attach/detach), matching the existing person/lead pattern
- [ ] **Organization tag UI:** Add `<x-admin::tags>` component to organization view page
- [ ] **Tag→mailing-list filter:** Add tag-based filtering when creating/editing a mailing list — e.g., "include persons tagged with X" or "include organizations tagged with Y"
- [ ] **Dynamic subscriber resolution:** When a campaign targets a mailing list that has tag filters, resolve subscribers dynamically (persons whose tags match OR persons belonging to tagged organizations)
- [ ] **Tag filter UI in mailing lists:** Add multi-select tag picker to the mailing-list create/edit form, stored as JSON or pivot
- [ ] **Campaign engine update:** `Campaign::getPersons()` already handles mailing-list-based scoping; extend to respect tag filters

### Ongoing Compliance

- [ ] Annual HIPAA risk assessment
- [ ] Quarterly access review
- [ ] Monthly audit log review
- [ ] Penetration testing (annual or after major changes)
- [ ] Security awareness training for all users
- [ ] Software update/patch management process

---

## AWS Lightsail Architecture Notes

```
Internet → Lightsail Instance (Nginx/Apache + PHP-FPM)
             ├── Certbot (Let's Encrypt — auto-renew)
             ├── App: Laravel 12 + Krayin CRM
             ├── Redis (to be provisioned)
             │   ├── bind 127.0.0.1 (not public)
             │   ├── requirepass <strong-password>
             │   └── RDB/AOF persistence
             ├── Queue Worker (supervisor → php artisan queue:work)
             ├── Sessions (Redis, once migrated)
             └── Cache (Redis, once migrated)
                                              ↓
                                    RDS (MySQL 8.0+, KMS-encrypted)
                                              ↓
                                    SES → Recipients
```

## Redis Setup Notes

Yes — since Redis runs on the same Lightsail instance, use `127.0.0.1`:

```
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=<your-redis-password>
REDIS_PORT=6379
```

**Critical Redis config (`/etc/redis/redis.conf`):**
```
bind 127.0.0.1          # DO NOT bind to 0.0.0.0 or public IP
requirepass <strong>    # mandatory — even on localhost
save 900 1              # RDB persistence
save 300 10
save 60 10000
appendonly yes          # AOF persistence for durability
```

**PHP Redis client:** Install `php8.3-redis` (phpredis extension) or fall back
to `predis/predis` via composer. phpredis is faster.

**Supervisor config for queue worker** (`/etc/supervisor/conf.d/laravel-worker.conf`):
```
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
user=ubuntu
redirect_stderr=true
stdout_logfile=/path/to/app/storage/logs/worker.log
```
