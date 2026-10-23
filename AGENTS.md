# AGENTS.md

Operational notes for anyone (human or agent) working in this repository.

Read these first:

1. `MIGRATION_PLAN.md` — decisions, scope, runbook, non-goals.
2. `MIGRATION_LOG.md` — append-only record of what was actually done and verified.
3. This file — environment, commands, invariants, and traps.

Do not re-open a locked decision in `MIGRATION_PLAN.md` without new evidence.
Add new work to `MIGRATION_LOG.md`; keep the plan decision-focused.

## Project state

- Legacy ADD application, originally PHP 5.6 / Ubuntu 16.04 / CodeIgniter 2.1.3.
- Target: PHP 8.3 on Ubuntu 24.04, with MySQL 5.7.33 for staging parity.
- Phase 1 (restructure), Phase 3 (dependencies), and local Phase 4 work are
  complete.
- Static audit: 0 syntax blockers.
- CLI boot and local web smoke (including authenticated login) pass on the VM.
- Remaining work is VPN-only: live layout/config capture, porting, and
  production-data validation.

## Repository layout

```
/local/add
  user/                 # first-party source packages (tracked)
  vendor/               # Composer output (ignored)
  work/                 # runtime module tree, built from user/ (ignored)
  modules/              # legacy installed tree; only modules/live may remain (ignored)
  config/               # deployment-specific profiles (ignored)
  tools/                # audit, module-tree build, patch application, seed scripts
  patches/              # tracked third-party patches
  composer.json         # path repositories under user/*/*
  composer.lock         # pinned
  MIGRATION_PLAN.md
  MIGRATION_LOG.md
  AGENTS.md
```

`user/<vendor>/<package>` is the source of truth. Composer mirrors these into
`vendor/`. The app runtime loads module definitions from `work/<vendor>/<package>`,
which `tools/build-module-tree.sh` builds.

## Host vs VM

The host has no global `php` or `composer`. Use the local PHP 8.3 wrapper:

```bash
/local/tools/php8.3 -v
/local/tools/php8.3 /local/tools/composer.phar --version
```

The host PHP is intentionally minimal (no curl/mbstring/zip). Composer commands on
the host therefore need `--ignore-platform-reqs`. The VM has the full extension
set; prefer the VM for real installs and smoke tests.

VM access:

```bash
/local/vm/add-php83/start.sh
/local/vm/add-php83/stop.sh
ssh -i /local/vm/add-php83/id_ed25519 -p 2222 rob@127.0.0.1
```

The VM is QEMU/KVM, Ubuntu 24.04.5, 4 vCPU, 8 GB RAM, 60 GB system disk, 100 GB
data disk. `/mnt/host_add` is a read-only 9p share of the host checkout.
`/srv/add-data` is the data disk; `/local/reports` and `/media/uhl_data` point
into it.

## Composer rules

- Never run a blanket `composer update`.
- Path packages are under `user/*/*`; versions are pinned in the root
  `composer.json` `repositories[].options.versions` map.
- Update only the target package(s), without `--with-dependencies` unless
  required, and inspect `composer.lock` for unexpected pinned-package movement.
- The root lock intentionally has no Composer plugin. `clinicle/install` is a
  plain library; do not reintroduce the Composer installer.
- On the host, add `--ignore-platform-reqs`. On the VM, do not.
- The local Composer may block abandoned packages with security advisories. For a
  controlled local update, use `--no-blocking`; do not make this the default for
  production builds.
- After changing `user/clinicle/web` or running Composer install/update, rebuild
  symlinks:

```bash
cd /local/add
tools/build-module-tree.sh          # symlinks
# or
tools/build-module-tree.sh --copy   # deployment copy
```

- After Composer install/update, apply third-party patches:

```bash
tools/apply-patches.sh
```

The script is idempotent. It currently applies
`patches/log4php-2.3.0-each.patch`.

The first-party library adapters are also captured as review/provenance patches
under `patches/` (`third-party-adapters.patch` plus per-library files). The
current `user/` tree is authoritative; do not apply the adapter patches to it.
They are not used by `tools/apply-patches.sh`; see `patches/README.md`.

## Audit and verification

Static audit:

```bash
cd /local/add
PHP_BIN=/local/tools/php8.3 JOBS=8 tools/php82-audit.sh --first-party
```

Expected: `syntax blockers: 0`.

Full audit (includes third-party vendor):

```bash
PHP_BIN=/local/tools/php8.3 tools/php82-audit.sh
```

CLI on the VM:

```bash
cd /local/add
ADD_PATH=/local/add ADD_PROFILE=local php vendor/bin/clinicle list
```

PHP built-in server (quick smoke only):

```bash
cd /local/add/vendor/clinicle/web/public
ADD_PATH=/local/add ADD_PROFILE=local \
  php -S 127.0.0.1:8080 -t . /local/add/tools/php-builtin-router.php
```

Apache is the authoritative SAPI because CI relies on `.htaccess`. A preview
vhost is installed on the VM at `/etc/apache2/sites-available/add-preview.conf`,
listening on `127.0.0.1:8082`:

```apache
<VirtualHost *:8082>
    DocumentRoot /local/add/vendor/clinicle/web/public
    DirectoryIndex index.php
    <Directory /local/add/vendor/clinicle/web/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    SetEnv ADD_PATH /local/add
    SetEnv ADD_PROFILE local
</VirtualHost>
```

`mod_rewrite` must be enabled. CI's `Log` class calls `chmod()` on
`application/logs`; the preview Apache workers run as `rob` (via
`APACHE_RUN_USER`/`APACHE_RUN_GROUP` in `/etc/apache2/envvars`) and the writable
dirs are `rob:rob`:

```bash
sudo chown -R rob:rob \
  vendor/clinicle/web/public/application/logs \
  log tmp vendor/clinicle/web/public/cache
```

If Apache stays as `www-data`, it cannot chmod files owned by `rob` and CI
returns an error page.

Test with `curl http://127.0.0.1:8082/`. The built-in server remains useful for
quick checks but does not apply `.htaccess`.

The host tunnel maps `127.0.0.1:8081` to the VM’s `127.0.0.1:8080`:

```bash
ssh -N -L 127.0.0.1:8081:127.0.0.1:8080 \
  -i /local/vm/add-php83/id_ed25519 -p 2222 \
  -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null \
  rob@127.0.0.1
```

Tunnel service name in the agent runtime: `add-php83-web`.

Local URL: `http://127.0.0.1:8081/`.

Legacy login behavior is unchanged and **outside the PHP 8.2 migration scope**:

- `Auth::login` builds the login view data but redirects to `/` instead of
  rendering `auth/login.php`.
- `Content::index` has no auth gate; unauthenticated `/` renders the shell.
- `auth/login` therefore redirects to `/`.
- `auth/login.php` already contains the normal header/footer, but the controller
  does not reach it.
- Verified under Apache 2.4 + mod_php 8.3 with `AllowOverride All`: `/` returns
  the 2,844-byte Home shell; `/auth/login` sends `Refresh: 0;url=/`. No PHP file
  in `user/` or `vendor/` loads `auth/login.php`.

The Apache rewrite is therefore not the missing piece. Do not “fix” that UX as
part of the PHP 8.2 port. If local authentication is
needed, POST directly:

```bash
curl -sS -c /tmp/c -b /tmp/c \
  -d 'identity=admin&password=PreviewAdmin123' \
  -o /dev/null http://127.0.0.1:8081/index.php/auth/login
curl -sS -b /tmp/c http://127.0.0.1:8081/index.php/auth/loggedin
```

CI's `redirect(..., 'refresh')` sends a `Refresh` header with HTTP 200 and an
empty body. Browsers follow it; curl needs `-D -` to see it or a manual follow.

## Local preview database

MySQL 5.7.33 runs in Docker on the VM, bound to `127.0.0.1:3306`:

```bash
sudo docker ps --filter name=add-mysql
sudo docker exec -it add-mysql mysql -uroot -paddroot
```

- Data volume: `/srv/add-data/mysql`
- Container: `add-mysql`, restart policy `unless-stopped`
- Root password: `addroot` (local preview only)
- Databases: `add_data`, `add_user`
- App user: `add` / `add`
- Start arguments include:
  `--character-set-server=utf8mb4 --collation-server=utf8mb4_general_ci`
  `--sql-mode=NO_ENGINE_SUBSTITUTION`

The permissive `sql_mode` is required by legacy `DISTINCT ... ORDER BY` queries.
Capture production `@@sql_mode` and match it before final validation.

Preview admin seed:

```bash
cd /local/add
PREVIEW_ADMIN_PASSWORD='PreviewAdmin123' php tools/seed-preview-admin.php
```

Local credentials: `admin` / `PreviewAdmin123`. Idempotent; re-running updates the
password hash and `last_changed`.

## Configuration traps

Local profile: `config/local` (ignored by git).

Required root-level settings include:

```ini
default_path=home
theme_module=add/theme
system_version=2.8.0
date_format=d/m/Y
image_path=vendor/clinicle/web/public/images
lrb_path=/local/add/user/leicrespbru/lrbtools
module_path=work
source_module_path=/local/add/user
```

`theme_module=add/theme` is required for the panel-based UI. Without it the app
falls back to `clinicle/web`’s `content.php`, which still has
`#main-content`/`#side-content`; the client JavaScript writes to
`#main-panel`/`#side-panel`, so home tools (including `LoginHomeTool`) are
received but never displayed.

`[Database]` must contain the PDO DSN/user/pass, for example
`mysql:host=127.0.0.1;charset=utf8;dbname=add_data`. `AbstractConnection` now
parses `dbname` and `host` up to `;`, rather than greedily to the end of the
DSN. Before that fix, parameters after `dbname` became part of the schema name
and migrations thought every table was missing.

`[SearchDatabase]` is also required by `clinicle/search`; for local preview it
can point at the same `add_data` database.

With `add/path` installed, `[Pathology]` must exist. Without it,
`PathologyStatusHomeTool` calls `getSetting()` on a null section and breaks the
home panel. For local preview:

```ini
[Pathology]
notification=Pathology transfer is disabled in the local preview.
```

`[PatientLookup]` is set to `MockPMI` for local work.

`ion_auth` resolves groups through `users_roles` → `roles_groups` → `groups`,
not directly through `users_groups`. The preview seed script now creates the
`admin` role mapping; without it `is_admin()` is false and pages such as
`/search` deny access.

INI sections persist until the next section header. A setting appended after
`[PatientLookup]` belongs to that section, not the root. This caused
`default_path` and `hg_binary` errors during setup.

`hg_binary` is no longer used. Version lookup is:

- system: `system_version` setting;
- modules: `VERSION` file, then `composer.json` version.

Do not reintroduce Mercurial or `.hg` metadata.

## Known PHP 8 traps already handled

- `web_init.php` must not end with `?>`; trailing output breaks session headers.
- CI `Security::xss_clean` uses `foreach`, not `each()`.
- CI `Security::entity_decode` uses `preg_replace_callback`, not `/e`.
- CI `Input::valid_ip()` passes `0` to `filter_var`, not `''`.
- CI DB config uses `mysqli` and `mysqli_report(MYSQLI_REPORT_OFF)`.
- `ADD_Tool_ErrorHandler` and `ClinicLE\Common\ErrorHandler` ignore
  `E_DEPRECATED`/`E_USER_DEPRECATED`; shutdown handlers act only on real fatal
  error types.
- CLI command runners ignore deprecations.
- First-party nested ternaries are parenthesised.
- `ADD_Page::submitForm()` treats `beforeSubmit()` as optional; it may return
  `null`, so do not call `count()` without a guard.
- `System\Application` treats `getErrors()` as optional; it may return `null`.
- `Profiler.php` uses `$this->{'_compile_'.$section}`, not array access.
- PHPWord uses `TemplateProcessor`.
- FPDF is `setasign/fpdf` and `ADD_Tool_PDF extends \FPDF`. Do not use
  `fpdf/fpdf`; that package is a Laravel wrapper exposing `Fpdf\Fpdf`.
- PhpSpreadsheet adapters use
  `getCell(Coordinate::stringFromColumnIndex($col + 1).$row)`.
- TCPDF 6.11 needs the curl extension at class load.
- `tools/build-module-tree.sh` must run after a fresh `clinicle/web` install;
  Composer install removes the web/view symlinks.

## Data and security invariants

- Production data must not leave the VPN.
- The local VM uses preview data only. Never import production rows, patient data,
  or `/media/uhl_data` into the local host/VM.
- Do not commit `config/`, dumps, logs, or patient data. They are gitignored; keep
  it that way.
- The target VM may not be re-provisionable due to permissions. Do not perform an
  in-place Ubuntu 16.04 → 24.04 upgrade. Fallbacks are recorded in the plan.
- CodeIgniter stays at 2.1.3 and stays in `vendor/clinicle/web/public`; do not move
  the web root or upgrade CI as part of this migration.

## Git

- Root repo is the monorepo. `vendor/`, `work/`, `modules/`, `config/`, `reports/`,
  logs, and temp data are ignored.
- `user/` is tracked. Commit dependency and code changes separately.
- Rollback archive before the restructure:
  `/local/backups/add-pre-restructure-20261023.tgz`.
- If `vendor/` or `work/` are changed, do not commit them; rebuild them.

## Quick smoke checklist

```bash
cd /local/add

# 1. Static audit
PHP_BIN=/local/tools/php8.3 JOBS=8 tools/php82-audit.sh --first-party

# 2. CLI on VM
ssh -i /local/vm/add-php83/id_ed25519 -p 2222 rob@127.0.0.1 \
  'cd /local/add && ADD_PATH=/local/add ADD_PROFILE=local php vendor/bin/clinicle list'

# 3. Legacy auth redirect (expected: Refresh to /)
curl -sS -D - -o /dev/null http://127.0.0.1:8081/auth/login | grep -i Refresh:

# 4. Login round-trip (direct POST; no login form in the legacy flow)
curl -sS -c /tmp/c -b /tmp/c \
  -d 'identity=admin&password=PreviewAdmin123' \
  -o /dev/null http://127.0.0.1:8081/index.php/auth/login
curl -sS -b /tmp/c http://127.0.0.1:8081/index.php/auth/loggedin
```

Expected: 0 audit blockers, CLI command list, and `{"logged_in":true}` after the
direct POST. The unauthenticated root/blank-shell behavior is legacy, not a
PHP 8.2 regression.
