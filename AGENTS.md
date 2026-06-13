# Krayin CRM: Agent Instructions

This repository ships reusable **agent skills** for developing Krayin CRM. These
instructions apply to all AI coding agents (Claude Code, Codex, Copilot, Cursor,
Kilo Code, etc.).

---

## Framework Context

- **Product:** Krayin CRM (Webkul) — an open-source Laravel CRM.
- **Stack:** Laravel `^12.0`, PHP `^8.3`, Pest `^3.0`, Laravel Pint `^1.18`.
- **Follow Krayin's own conventions** — use the patterns already present in this
  repository's modules; do not import patterns from other Laravel products.
- **Module location:** `packages/Webkul/{ModuleName}/src/`
- **Core modules (reference implementations):** `Lead`, `Contact`, `Activity`,
  `Quote`, `Product`, `User`, `Attribute`, `Core`, `Admin`, `DataGrid`.

---

## Skills

Workspace skills live in `.github/skills/` (canonical source) and are symlinked
into every agent runtime (`.ai`, `.claude`, `.codex`, `.cursor`, `.kilocode`).
See [.github/skills/README.md](.github/skills/README.md) for the standard.

| Skill | Use when |
|-------|----------|
| `crm-package-development` | Creating a new CRM package/module or extending CRM functionality without touching core. |
| `pest-testing` | Writing or debugging unit/feature tests with Pest. |

Read the relevant `SKILL.md` before starting work in its domain. Do not restate
skill content here — keep details in the skills.

---

## Critical Conventions (Never Deviate)

- **Never modify core Krayin files** unless explicitly required. Extend behavior
  through a package under `packages/Webkul/`.
- **All schema changes go through migrations** — never edit the database directly.
- **Follow the existing module layout** (Providers, Models, Contracts,
  Repositories, Http/Controllers, Routes, Database/Migrations, Resources/views,
  Config). The `crm-package-development` skill documents the full structure.
- **Repository pattern:** models are accessed through repositories bound via
  contracts in the service provider — match the surrounding modules.
- **Preserve backward compatibility** so the CRM stays upgrade-safe.

---

## Development Cycle

```bash
composer install            # install PHP dependencies
php artisan migrate          # run migrations
php artisan test --compact   # run the Pest test suite
./vendor/bin/pint            # format code (PSR-12 via Laravel Pint)
```

- Add tests for new behavior; follow the `pest-testing` skill.
- Run `./vendor/bin/pint` before committing — CI applies Pint formatting.
- Validate the skills setup with `bash bin/validate-skills.sh`.

---

## Create Form Troubleshooting (Contacts/Persons)

The standalone person create form (`/contacts/persons/create`) had extensive
debugging. Key findings that may apply to other create/edit forms:

### `entity_type` Added by Controller Constructor
- `PersonController::__construct()` calls
  `request()->request->add(['entity_type' => 'persons'])` — the `entity_type`
  parameter is NOT rendered as a hidden form field; it's injected via the
  controller constructor before validation runs.
- The `AttributeForm::rules()` method queries attributes by `request('entity_type')`,
  so this must be set before form request validation fires.

### Native `<form>` vs `<x-admin::form>`
- `<x-admin::form as="form">` wraps a `<v-form>` (Vee-Validate) component that
  intercepts submission via `@submit.prevent`. If Vue fails to compile or
  Vee-Validate rules mismatch server rules, the form silently does nothing.
- **Fix:** replace `<x-admin::form as="form">` with a native `<form method="POST" action="...">`
  + `@csrf` + plain `<button type="submit">` (no `@click.prevent`). This
  bypasses all Vee-Validate frontend interception.

### `::name` Attribute Handling (`control.blade.php`)
- Blade components (e.g. `<x-admin::form.control-group.control>`) receive
  `::name="\`${expr}\`"` as key `:name` in `$attributes` (single colon).
- The `control.blade.php` component must:
  1. Extract `$attributes->get(':name')` into `$dynamicName`
  2. Strip it with `$attributes->except([':name'])`
  3. Output `:name="{!! $dynamicName !!}"` on BOTH `<v-field>` and `<input>`
- Outputting `::name` twice (duplicate `:name`) on `<v-field>` causes an
  infinite JS render loop ("slowing down" in Firefox).

### `v-model` Placement
- When `<input>` is inside `<v-field v-slot="{ field }">`, putting `v-model` on
  `<v-field>` causes the input's DOM `value` property to be the whole field
  object (`[object Object]`), not the user's string.
- **Fix:** always place `v-model` on the actual `<input>`/`<select>` element,
  not on `<v-field>`. This applies to `text`, `textarea`, and `select` cases
  in `control.blade.php`.

### Email/Phone Label Not Submitted
- The email/phone Vue components render a `<select>` for `label` (with
  `::name="\`${code}[${index}][label]\`"`). When the form is submitted natively
  (not via Vee-Validate), this select's value is **not included** in the POST
  body — Vee-Validate's `<v-field>` manages the select's name and the native
  browser serialization may not pick it up.
- **Fix:** make `emails.*.label` and `contact_numbers.*.label` `'nullable'` in
  `AttributeForm::rules()` (instead of `$attribute->is_required ? 'required' : 'nullable'`).

### Encrypted Column Types
- Columns using `'encrypted'` or `'encrypted:array'` Eloquent casts store raw
  ciphertext. They must use `text` (or `longtext`) column type — `json` type
  rejects encrypted strings in MySQL 8.0+.
- **Affected columns (`json→text` migrations):** `attribute_values.json_value`,
  `activities.additional`, `emails.from`, `emails.sender`, `emails.reply_to`,
  `emails.cc`, `emails.bcc`, `emails.reference_ids`.
- **Affected columns (`string→text` migration):** `emails.subject`,
  `emails.name` — `varchar(255)` is too short for encrypted payloads (can
  exceed 300 chars for even short subjects).
- Datagrids using `DB::table()` (not Eloquent) display raw ciphertext. Use
  `decrypt($value, false)` inside closures to display decrypted values.
- Known datagrids needing decrypt closures: `EmailDataGrid` (name, from,
  subject, reply).

### `@pushOnce` Keys
- Multiple Blade components used `@pushOnce('scripts')` without a key — only the
  first push was rendered. Add unique keys like `@pushOnce('scripts', 'v-email-component')`.

### Laravel 12 Encrypted Cast Behavior
- `'encrypted'` cast uses `encrypt($value, false)` (no PHP serialization).
- `'encrypted:array'` cast uses `Json::encode` + `Crypt::encryptString`.
- Both require `decrypt($value, false)` for manual decryption.
