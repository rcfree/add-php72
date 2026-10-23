# ADD PHP 8.3 Migration Plan

Status: Phase 1 restructure complete locally; dependency migration next.
Last updated: 2026-10-23.

This document records the decisions already made so the migration does not restart from
first principles. Do not re-open a locked decision without new evidence.

Execution state, VM details, and run results live in `MIGRATION_LOG.md`. This file is
for decisions, scope, and the runbook only; do not add progress entries here.

## 1. Locked decisions

| Area | Decision |
|---|---|
| Target OS | Ubuntu 24.04 VM on existing infrastructure |
| Migration method | Fresh VM, not an in-place 16.04 → 24.04 upgrade. Keep the 16.04 host as rollback. |
| Target PHP | PHP 8.3 (distro packages), not 8.2 |
| Current runtime | PHP 5.6, Ubuntu 16.04 |
| Database | MySQL 5.7.33, run as a Docker container (`mysql:5.7.33`) because Ubuntu 24.04 does not package 5.7. Data lives at `/srv/add-data/mysql`; preview dumps are imported. |
| Data handling | No production data leaves the VPN. The local VM is a preview-data fixture; final validation runs in the VPN against the real/staging database after the code is ported. |
| Web stack | Apache 2.4 + `libapache2-mod-php8.3`; docroot is the CI public directory |
| Web root | Keep `vendor/clinicle/web/public`; an optional `/local/add/public` symlink to it is allowed. Do not relocate CodeIgniter in this migration. |
| Framework | CodeIgniter 2.1.3, embedded in `clinicle/web`; keep and patch, do not upgrade |
| First-party ownership (current paths) | `modules/`, `vendor/clinicle/`, `vendor/leicrespbru/`; target is `user/` |
| `clinicle/install` | Keep as a runtime library for dictionary/form setup and `InstallEvents`; drop the Composer installer/plugin; reduce `InstallCommand` to `installForms()`; do not install module code |
| Source control | New root git monorepo; remove `.hg` directories from first-party packages |
| Layout | Move first-party packages to `user/<vendor>/<package>`; Composer path repositories |
| Restructure order | Restructure first, then PHP 8 changes |
| Composer | Composer 2 for the build; do not use the bundled Composer 1.8.5 |
| Dependency strategy | Hybrid: upgrade maintained libraries, replace PHPExcel, pin and patch the rest |
| PHPWord | Upgrade 0.11.1 -> 1.4.x; adapt the single template wrapper |
| TCPDF | Upgrade 6.2.26 -> 6.11.x; keep 6.x, do not jump to 7.x |
| FPDF | Replace `itbz/fpdf` 1.7.2 with `fpdf/fpdf` 1.8+/1.90; adapt the one subclass |
| Minify | Upgrade 1.3.61 -> 1.3.75 |
| PHPExcel | Replace with PhpSpreadsheet 5.x in the two read-only adapters |
| Symfony | Pin 2.8/3.0; verified working on PHP 8.3 with deprecation notices only |
| Guzzle | Pin 3.9; remote-monitor integration deferred |
| log4php | Pin 2.3.0; one `each()` patch |
| Pimple, UUID, RandomLib, json-schema | Keep pinned; smoke tests pass |
| PMI integration | In scope: PDO_DBLIB / SQL Server / FreeTDS |
| R + rspiro | Deferred |
| CiviCRM / lrbtools Civi | Not required |
| Baseline | User-run staging inside VPN; no direct old-host access for the migration work |
| Previously missing packages | All six are now present via `/local/add_modules` and copied into `modules/` with VCS metadata stripped. Path-repo versions come from `composer.lock`: `add/inv` 2.2.4, `add/med` 1.4.2, `clinicle/search` 0.1.0, `ember/asthcopd` 1.4.0, `ember/blness` 1.7.3, `ember/pneumonia` 1.1.0. |

## 2. Evidence collected

Detailed run output and environment changes are in `MIGRATION_LOG.md`; this section is
the decision-relevant summary.

### 2.1 Static audit

Tool: `tools/php82-audit.sh` (lint + PHPCompatibility 8.2 + triage).
Run against this checkout with PHP 8.3.6:

- 5,066 PHP files total; 1,527 first-party.
- `modules/`: 710 files, **0 syntax errors**. Business modules are already parse-clean.
- 44 non-test syntax blockers:
  - 4 first-party framework files.
  - 21 PHPExcel files.
  - 12 PHPWord files.
  - 7 TCPDF files.
- 168 removed-API hits, mostly in unused CI database drivers (mysql/mssql/sqlite) and
  CI Xmlrpc/Encrypt.
- `phpcbf` trial on copies of PHPExcel + TCPDF: 293 violations fixed in 26 files,
  and **0 remaining syntax failures**.

### 2.2 Runtime smoke tests on PHP 8.3

Passed:
- Symfony 2.8 `event-dispatcher`, `http-foundation`, `filesystem`, `finder`, `console`.
- Pimple 3.2.
- log4php 2.3 configure + log.
- Minify JS.
- Ramsey UUID 2.9.
- RandomLib 1.2.
- JSON Schema 5.2.
- Wrep daemonizable class load.
- Guzzle 3 class load.

Failed:
- FPDF: `Call to undefined function fpdf\get_magic_quotes_runtime()` in the constructor.
- Minify CSS: `implode(): Argument #2 ($array) must be of type ?array, string given`
  at `vendor/matthiasmullie/minify/src/CSS.php:528`.
- PHPExcel, TCPDF, PHPWord: parse fatal before runtime.

### 2.3 Repository and dependency facts

- `vendor/` and `modules/` are both hgignored; the hg repositories no longer exist.
- `composer install` is not reproducible from this checkout:
  - unauthenticated Bitbucket VCS URLs return 404;
  - the satis host is unreachable.
- `clinicle/install` is currently a Composer 1 plugin (`composer-plugin-api ~1.0`,
  `composer/composer ^1.0`). It is being kept as a runtime library for dictionary/form
  setup and `InstallEvents`; only the Composer installer/plugin is being removed. The
  Composer 2 plugin compatibility problem therefore disappears.
- `modules/add/research/composer.json` has a typo: `tenickcom/tcpdf`; correct it to
  `tecnickcom/tcpdf`.
- The runtime module tree is `work/` (`module_path=work`). The removed Composer
  installer previously populated `modules/` and generated web/view symlinks. Capture
  the live `work/` layout and replicate it explicitly; do not rely on Composer's
  default install path.
- `modules.ini` is dead at runtime. The only reader/writer is
  `ComposerModuleManager`, which is being deleted. Runtime aliases come from
  `Module.php:37-42` (`page.ini` `[alias]`, falling back to the last directory-name
  segment) and are resolved in memory by `ModuleCollection`. `modules/live/modules.ini`
  is not read by any code.

## 3. Target layout

```
/local/add
  composer.json              # path repositories for user/*
  composer.lock              # pinned, generated by Composer 2
  MIGRATION_PLAN.md          # decisions, scope, runbook
  MIGRATION_LOG.md           # execution log
  .gitignore
  user/
    add/...                  # from modules/add/*
    clinicle/...             # merge modules/clinicle/* and vendor/clinicle/*
    cdbrkr/...
    copd/main/               # from modules/main2
    ember/...                # hfail now; other ember packages captured later
    leicrespbru/lrbtools/    # from vendor/leicrespbru/lrbtools
  patches/
    log4php-2.3.0-each.patch
  tools/
    php82-audit.sh
    build-module-tree.sh
    apply-patches.sh
    restructure-user.sh
  vendor/                    # generated, gitignored
  modules/                   # legacy installed module tree; no longer populated by Composer
  work/                      # runtime module tree: work/<vendor>/<package>, generated/gitignored
  public -> vendor/clinicle/web/public   # optional docroot symlink
  config/                    # deployment-specific, not committed
```

The Composer installer previously populated `modules/` and generated web/view symlinks.
It is being removed. Capture the live `work/` layout and the web/view symlink targets,
then replicate them from `user/` with a deterministic build step. The runtime loader
resolves modules from `work/`, so that layout must match the baseline even though module
code is no longer installed by Composer. Do not generate `modules.ini`; nothing reads it
at runtime.

The verified layout is `work/<vendor>/<package>` (for example `work/add/qol`,
`work/clinicle/core`). `ModuleSetup::extractModuleMap` runs
`Finder::directories()->depth('==1')->in($workPath)`; with Symfony 2.8 this returns the
`work/<vendor>/<package>` directories, not the vendor directories. Populate exactly that
shape.

`user/*/*` path repositories use explicit versions so the existing `^1.x` constraints
still resolve. Composer supports this, verified with Composer 2.10:

```json
{
  "type": "path",
  "url": "user/*/*",
  "options": {
    "symlink": false,
    "versions": {
      "clinicle/core": "2.17.2",
      "clinicle/common": "1.4.0",
      "copd/main": "1.0.0"
    }
  }
}
```

Generate the full `versions` map from `composer.lock`. For `x-dev` entries use a stable
normalised version that satisfies the root constraint.

## 4. Execution plan

### Phase 0 - Baseline and capture

Run inside the VPN, on the staging side.

1. Capture from the live/staging host:
   - `php -v`, `php -m`, `php --ini`;
   - Apache version and loaded modules;
   - MySQL client and **server** versions, plus
     `SELECT VERSION(), @@version_comment, @@sql_mode, @@character_set_server,
     @@collation_server;`;
   - schema-only database metadata (table definitions, row counts, `sql_mode`);
     do **not** copy production rows outside the VPN;
   - FreeTDS configuration and SQL Server/TDS version for PMI;
   - `/local/add/config/<profile>/` contents;
   - the live `work/` tree and web/view symlink targets;
   - `page.ini` alias values for every active module;
   - cron entries and systemd units, especially resphy import and cleanup;
   - mounts and permissions for `/local/reports` and `/media/uhl_data`.
2. Capture the live `vendor/` tree, specifically `add/inv`, `add/med`,
   `clinicle/search`, `ember/asthcopd`, `ember/blness`, and `ember/pneumonia`, plus
   their `composer.json` versions. These six are absent from this checkout and are
   required for Composer path-repository resolution.
3. Archive the current application, `vendor/`, `modules/`, and `config/`. Do not copy
   patient data or `/media/uhl_data` outside the VPN.
4. Build the local baseline output set using the preview database only; production-data
   golden outputs must be captured inside the VPN:
   - authenticated HTTP responses for login, patient search/record, and one page per module;
   - FPDF report PDFs;
   - PHPWord letter DOCX files;
   - TCPDF barcode label PDFs;
   - XLSX imports (store parsed rows, not just the file);
   - minified CSS/JS;
   - CSV exports.
5. Store baseline artifacts under `tests/golden/old/` and keep them out of `user/`.

### Phase 1 - Restructure to user/ and git

Do this before any PHP 8 code edits.

1. Create `.gitignore` and initialise the root git repository. Ignore at least
   `vendor/`, `backup/`, `work/`, `modules/`, `log/*`, `tmp/*`, `search/*`,
   `cache/*`, `reports/`, and deployment-specific `config/`. Track `user/`,
   `composer.json`, `composer.lock`, `tools/`, `patches/`, `MIGRATION_PLAN.md`, and
   `MIGRATION_LOG.md`. Commit the current state before moving anything.
2. Remove `.hg`, `.hgtags`, `.hgignore` from all first-party package directories.
3. Remove stale nested vendors and locks:
   - `modules/add/research/vendor`
   - `modules/add/pr/vendor`
   - `modules/add/pr_dashboard/vendor`
   - `modules/add/research_api/composer.lock`
4. Capture the six deferred packages from live into:
   - `user/add/inv`, `user/add/med`, `user/clinicle/search`
   - `user/ember/asthcopd`, `user/ember/blness`, `user/ember/pneumonia`
   Do not modify them yet; they only need to exist for Composer resolution.
5. Move:
   - `modules/add/*` -> `user/add/*`
   - `modules/clinicle/*` -> `user/clinicle/*`
   - `modules/cdbrkr/*` -> `user/cdbrkr/*`
   - `modules/ember/hfail` -> `user/ember/hfail`
   - `modules/main2` -> `user/copd/main`
   - `vendor/clinicle/*` -> `user/clinicle/*`
   - `vendor/leicrespbru/lrbtools` -> `user/leicrespbru/lrbtools`
6. Leave `modules/live/` alone; it is runtime metadata, not a package.
7. Update root `composer.json`:
   - remove dead Bitbucket and satis repository entries;
   - add the `user/*/*` path repository with explicit versions;
   - do not add an `allow-plugins` entry; `clinicle/install` becomes a normal library.
8. Fix `modules/add/research/composer.json` (`tenickcom` -> `tecnickcom`).
9. Convert `clinicle/install` from a Composer plugin to a plain library:
   - set `"type": "library"` (or remove the type);
   - remove `composer-plugin-api`, `composer/composer`, `extra.class`, the
     `post-autoload-dump` script, the satis repository, and `secure-http: false`;
   - delete `ComposerModuleInstaller.php`, `ComposerModuleInstallerPlugin.php`, and
     `ComposerModuleManager.php`;
   - keep `Command/InstallCommand.php`, `ModuleInstall.php`, `Event/*`,
     `Dictionary/*`, and `Resource/*` so dictionary/form setup still works;
   - reduce `Command/InstallCommand.php` to dictionary/form setup: remove
     `composer require/update`, module copy/symlink, backup/remove, and
     `composer dump-autoload`; resolve the module from `work/` or `user/` and call
     `ModuleInstall::installForms()` only. This reduction is deferred to Phase 4;
     the command is currently unregistered, so it cannot install code as-is;
   - remove or leave unreachable `ModuleInstall::installCode()`, `copyModule()`,
     and `createSymLinks()`; do not call them from the build or commands;
   - keep the `clinicle/install` requirements in the module `composer.json` files.
   No Composer plugin metadata is needed.
9a. Define the module-tree build without the installer:
   - create `work/<vendor>/<package>` for each package in `user/<vendor>/<package>`
     (symlink for staging, copy for a self-contained deployment);
   - create the web/view symlinks for packages that contain `web/` or `views/`,
     targeting the same locations the old installer used:
     `vendor/clinicle/web/public/module/<vendor>/<package>` and
     `vendor/clinicle/web/public/application/views/module/<vendor>/<package>`;
   - verify `ModuleSetup::extractModuleMap` sees the same module set as the baseline,
     and that `page.ini` aliases resolve through `Module.php`/`ModuleCollection`;
   - do not generate `modules.ini` and do not install module code via Composer.
10. Regenerate the lock for the path repositories without upgrading third-party
    packages. `install` cannot do this because the package sources changed:
    ```bash
    export ADD_PATH=/local/add
    export ADD_PROFILE=<profile>
    composer update --no-scripts \
      'clinicle/*' 'add/*' 'cdbrkr/*' 'copd/*' 'ember/*' 'leicrespbru/*'
    composer dump-autoload -o
    ```
    Do not run a blanket `composer update`; it would move Symfony and other pinned
    packages. Then run the module-tree build from step 9a and compare `work/`, its
    aliases, and the web/view symlinks with the baseline.
11. Boot the application on the old PHP 5.6 runtime if possible; otherwise treat this
    as the first staging gate on PHP 8.3.
12. Commit the restructure as a single reviewable change with no PHP 8 edits mixed in.

### Phase 2 - Ubuntu 24.04 VM and PHP 8.3

1. Provision the VM; install:
   ```bash
   libapache2-mod-php8.3 php8.3-cli php8.3-mysql php8.3-mbstring
   php8.3-xml php8.3-zip php8.3-gd php8.3-curl php8.3-sybase php8.3-opcache
   ```
   `php8.3-sybase` provides `pdo_dblib`; install FreeTDS.
1a. Install MySQL 5.7.33, matching production. Ubuntu 24.04 does not package 5.7,
    so use the official container:
    ```bash
    sudo apt-get install -y docker.io
    sudo mkdir -p /srv/add-data/mysql && sudo chown -R 999:999 /srv/add-data/mysql
    sudo docker run -d --name add-mysql --restart unless-stopped \
      -p 127.0.0.1:3306:3306 \
      -v /srv/add-data/mysql:/var/lib/mysql \
      -e MYSQL_ROOT_PASSWORD=addroot \
      mysql:5.7.33 \
      --character-set-server=utf8mb4 --collation-server=utf8mb4_general_ci
    ```
    Create `add_data` and `add_user` with `utf8`/`utf8_general_ci`, create the
    application user, and import the full production dump. The MariaDB preview
    dumps can be used only for a schema smoke test.
1b. Verify PHP extensions before configuring Apache. The Ubuntu 24.04 packages in
    this build left `/etc/php/8.3/mods-available/*.ini` empty, so `php -m` showed no
    `PDO`, `mysqli`, or `pdo_mysql`. If that happens, restore the templates:
    ```bash
    sudo find /usr/share/php8.3-* -name '*.ini' \
      -exec cp -f {} /etc/php/8.3/mods-available/ \;
    sudo systemctl restart apache2
    php -m | grep -E 'PDO|pdo_mysql|mysqli|mysqlnd|pdo_dblib'
    ```
2. Configure Apache:
   - docroot: `vendor/clinicle/web/public` (installed artifact), or
     `/local/add/public` if that symlink is created;
   - `AllowOverride All` for CI `.htaccess`;
   - `SetEnv ADD_PATH /local/add`;
   - `SetEnv ADD_PROFILE <profile>`.
   Do not physically move the CI `system/` or `application/` trees; the code
   hardcodes `vendor/clinicle/web/public/...` in `getWebAppPath()` and `Minifier`.
3. PHP settings:
   - `date.timezone=Europe/London`
   - `display_errors=Off`, `log_errors=On`
   - staging `error_reporting=E_ALL`; production `E_ALL & ~E_DEPRECATED` after triage
   - `memory_limit` at least the old value; `max_execution_time=3600`
   - writable `cache/`, `log/`, `tmp/`, `search/`, `dict/`
4. Mount `/local/reports` and `/media/uhl_data`; verify permissions.
5. Configure FreeTDS for PMI and verify:
   ```bash
   tsql -S <host> -U <user> -P <pass>
   php -r 'var_dump(extension_loaded("pdo_dblib"));'
   ```
6. Install Composer 2 separately. Do not use `vendor/bin/composer`.

### Phase 3 - Dependency migration

Apply as small, separate commits. Run `tools/php82-audit.sh` and the relevant smoke test
after each one.

Composer policy for this phase:

- Never run a blanket `composer update`.
- Edit the relevant `user/.../composer.json` constraint first.
- Update only the target package, with its new dependencies:

  ```bash
  composer update --no-scripts <vendor/package> --with-dependencies
  ```

- After each update, inspect `composer.lock` for unexpected Symfony, Guzzle, Pimple,
  or other pinned-package changes. Revert and retry with a narrower command if a pinned
  package moved.

#### 3.1 PHPWord 0.11.1 -> 1.4.x

- Change `user/clinicle/letter/composer.json` to `phpoffice/phpword: ^1.4`.
- Replace `new PhpWord(); $phpWord->loadTemplate($file)` with
  `new \PhpOffice\PhpWord\TemplateProcessor($file)` in
  `user/clinicle/letter/lib/ADD/Tool/RTF.php`.
- `setValue`, `getVariables`, `saveAs` remain available.
- Compare generated DOCX text and placeholder substitution against the baseline.

#### 3.2 TCPDF 6.2.26 -> 6.11.x

- Root constraint `^6.2` already allows 6.11; update only TCPDF.
- No application API changes expected; `write1DBarcode` is stable.
- Compare barcode label PDFs: page count, text, and barcode readability.

#### 3.3 FPDF 1.7.2 -> fpdf/fpdf 1.8/1.90

- Change `user/clinicle/report/composer.json` from `itbz/fpdf` to `fpdf/fpdf: ^1.8`.
- In `user/clinicle/report/lib/ADD/Tool/PDF.php`, remove `use fpdf\FPDF;` and extend
  the global class: `class ADD_Tool_PDF extends \FPDF`.
- Compare report PDFs page-by-page.

#### 3.4 Minify 1.3.61 -> 1.3.75

- Update `matthiasmullie/minify` to 1.3.75.
- Compare minified CSS and JS strings against baseline. This removes the
  `CSS.php:528` `implode` failure.

#### 3.5 PHPExcel 1.8.1 -> PhpSpreadsheet 5.x

- Change `user/clinicle/datafile/composer.json` and
  `user/leicrespbru/lrbtools/composer.json` from `phpoffice/phpexcel` to
  `phpoffice/phpspreadsheet: ^5.10`.
- Update:
  - `user/clinicle/datafile/src/ExcelDataFile.php`
  - `user/leicrespbru/lrbtools/lib/LRB/DataFile/Excel.php`
- Mapping:
  - `PHPExcel_IOFactory::load` -> `PhpOffice\PhpSpreadsheet\IOFactory::load`
  - `PHPExcel_Reader_Exception` -> `PhpOffice\PhpSpreadsheet\Reader\Exception`
  - `getCellByColumnAndRow($c, $r)` -> `getCell(Coordinate::stringFromColumnIndex($c + 1).$r)`
  - `getHighestDataColumn`, `getHighestRow`, `getValue` remain available.
- There are no PHPExcel writers in this application; both adapters are read-only.
- Compare parsed rows for every sample XLSX/XLS file against the PHP 5.6 baseline.
  Pay specific attention to dates, formula cells, empty cells, and numeric strings.

#### 3.6 Pinned libraries

- Symfony 2.8/3.0: no version change. Expect deprecation notices from missing return
  types; verify event dispatch, console commands, Finder, Filesystem, and
  Request/Response.
- Guzzle 3.9: no version change; remote monitoring remains deferred.
- log4php 2.3.0: apply `patches/log4php-2.3.0-each.patch` after Composer install.
- Pimple/UUID/RandomLib/json-schema: no changes.

### Phase 4 - Framework and CI patches

All paths below are post-restructure.

#### 4.1 Compile blockers

- `user/clinicle/core/src/Content/Render/HtmlElement.php:235`
- `user/clinicle/core/lib/ADD/Content/Html.php:231`
  Parenthesise the nested ternary as
  `is_object($html) ? $html : ($htmlArray ? $html : array(...))`.
- `user/clinicle/common/src/Log.php:36`
  Rewrite `outputDebug()` as a plain if/else. The original expression is lossy under
  PHP 7 left-associativity and is debug-only.
- `user/clinicle/web/public/system/libraries/Profiler.php`
  `$this->_compile_{$x}` -> `$this->_compile_[$x]` (phpcbf). Profiler is unused but
  keep the tree parse-clean.

#### 4.2 CI 2.1.3

- Profile config `config/<profile>/ci/database.php`:
  - `dbdriver` `mysql` -> `mysqli`;
  - add `mysqli_report(MYSQLI_REPORT_OFF);` before connection to preserve CI's
    pre-8.1 error-return behaviour.
- `user/clinicle/web/public/system/core/Security.php`:
  - `each()` -> `foreach` at `xss_clean` (line ~272);
  - `/e` modifier -> `preg_replace_callback` in `entity_decode` (lines ~512-513).
- `user/clinicle/web/public/system/database/drivers/mysqli/mysqli_driver.php`:
  remove the `mysql_escape_string` fallback at line ~320.
- `user/clinicle/web/public/index.php`:
  set `ENVIRONMENT` to `production` for live, or make it env-driven.
- `CodeIgniter.php` `set_magic_quotes_runtime` and `Input.php`
  `get_magic_quotes_gpc` are already guarded by `is_php()`; no change required.

#### 4.3 First-party cleanup

- Replace `$php_errormsg` / `track_errors` in:
  - `user/clinicle/core/lib/ADD/Tool/ErrorHandler.php`
  - `user/clinicle/common/src/ErrorHandler.php`
  - `user/clinicle/core/lib/ADD/Util.php`
  - `user/clinicle/monitor/src/Monitor.php`
  Use `error_get_last()` if the log detail matters.
- lrbtools Civi `split()` calls: not required for this migration; patch only if that
  path is ever re-enabled.
- Do not change the seven optional-before-required signatures in modules unless the
  audit or runtime shows a call site omitting the argument. Sampled call sites pass
  all arguments; PHP 8 treats them as required, which is a deprecation only here.

#### 4.4 Dynamic properties and deprecations

- PHP 8.2+ dynamic-property deprecations are non-fatal. Do not mass-edit 669 classes.
- Log deprecations in staging; suppress display in production.
- Add `#[AllowDynamicProperties]` only to a small number of base classes if log volume
  becomes operationally significant.

### Phase 5 - Validation

Run in this order.

Local validation uses the preview database only. After porting the code to the VPN,
repeat the HTTP/output matrix against the staging/production database there. Do not
import production data into the local VM.

1. Static:
   ```bash
   PHP_BIN=php8.3 tools/php82-audit.sh --first-party
   ```
   Acceptance: 0 syntax blockers. Investigate every `REMOVED_API` hit that is on a
   live path; unused CI drivers, Xmlrpc, Encrypt, and text_helper can be documented
   and ignored.
2. Build:
   ```bash
   composer install --no-scripts
   composer dump-autoload -o
   bash tools/apply-patches.sh
   ```
3. CLI:
   ```bash
   ADD_PATH=/local/add ADD_PROFILE=<profile> php vendor/bin/clinicle list
   ```
4. HTTP smoke matrix:
   - login/logout; failed login;
   - user create/edit (exercises `xss_clean`);
   - patient search, patient record, episode;
   - one page from each active module;
   - admin setup/install screens that are still used.
5. Output comparisons:
   - FPDF report PDFs;
   - TCPDF barcode labels;
   - PHPWord DOCX letters;
   - XLSX/XLS imports;
   - CSV exports;
   - minified CSS/JS;
   - normalised HTML for key pages.
6. Integrations:
   - PMI lookup over `pdo_dblib`;
   - FreeTDS/TDS version check.
   R/rspiro, Guzzle remote monitoring, and CiviCRM are out of scope.
7. Logs:
   - no `E_ERROR` or `E_WARNING` on the smoke paths;
   - deprecations reviewed and either fixed or explicitly accepted.

### Phase 6 - Cutover and rollback

1. Keep the old PHP 5.6 host and the old database available for rollback.
2. Deploy the built artifact plus `user/` source and `config/` to the new VM.
3. Run the smoke matrix against the staging database.
4. Schedule a short maintenance window for the live database cutover.
5. Switch the vhost/docroot and environment variables.
6. Retain the old host read-only until sign-off.
7. No database schema changes are part of this migration, so rollback is a web-root
   and configuration switch.

## 5. Change matrix

| File / area | Change |
|---|---|
| `composer.json` | Path repos for `user/*/*`, explicit versions, remove dead repos; no allow-plugins entry |
| `user/clinicle/install/composer.json` | Convert to a plain library; remove plugin API, scripts, satis |
| `user/clinicle/install/src/Composer*.php` | Delete Composer installer, plugin, and manager |
| `user/clinicle/install/src/Command/InstallCommand.php` | Reduce to dictionary/form setup; remove Composer/copy/symlink logic |
| `user/clinicle/install/src/ModuleInstall.php` | Keep `installForms()`; remove or leave unreachable `installCode()`/`copyModule()`/`createSymLinks()` |
| `user/add/research/composer.json` | Fix `tenickcom` typo |
| `user/clinicle/letter/composer.json` | `phpoffice/phpword: ^1.4` |
| `user/clinicle/letter/lib/ADD/Tool/RTF.php` | `TemplateProcessor` |
| `user/clinicle/report/composer.json` | `fpdf/fpdf: ^1.8` |
| `user/clinicle/report/lib/ADD/Tool/PDF.php` | Extend global `\FPDF` |
| `user/clinicle/datafile/composer.json` | `phpoffice/phpspreadsheet: ^5.10` |
| `user/clinicle/datafile/src/ExcelDataFile.php` | PhpSpreadsheet read adapter |
| `user/leicrespbru/lrbtools/composer.json` | `phpoffice/phpspreadsheet: ^5.10` |
| `user/leicrespbru/lrbtools/lib/LRB/DataFile/Excel.php` | PhpSpreadsheet read adapter |
| `user/clinicle/core/src/Content/Render/HtmlElement.php` | Ternary parentheses |
| `user/clinicle/core/lib/ADD/Content/Html.php` | Ternary parentheses |
| `user/clinicle/common/src/Log.php` | Rewrite debug output |
| `user/clinicle/web/public/system/libraries/Profiler.php` | Curly offset |
| `user/clinicle/web/public/system/core/Security.php` | `each`, `/e` |
| `user/clinicle/web/public/system/database/drivers/mysqli/mysqli_driver.php` | Remove `mysql_escape_string` fallback |
| `config/<profile>/ci/database.php` | `mysqli`, `mysqli_report` |
| `patches/log4php-2.3.0-each.patch` (new) | `each` -> `foreach` |
| `tools/php82-audit.sh` | Retarget from `modules` to `user` after the move |
| `tools/build-module-tree.sh` | Build `work/<vendor>/<package>` and web/view links from `user/` |
| `user/clinicle/core/src/Command/CommandRunner.php` | Ignore `E_DEPRECATED`/`E_USER_DEPRECATED`; dynamic properties are not fatal |
| `user/clinicle/manage/src/Runner/CommandRunner.php` | Same deprecation guard |
| `tools/apply-patches.sh` (new) | Idempotent third-party patch application |
| `tools/restructure-user.sh` (new) | One-time move and cleanup |

## 6. Risks and open items

| Risk | Mitigation |
|---|---|
| Production data cannot leave the VPN | Local validation uses preview data; final validation and production-data golden outputs run in the VPN after porting. Never import production rows into the local VM. |
| Target VM re-provisioning permission is unconfirmed | User will resolve. Fallbacks if a new VM is not allowed: containerized PHP 8.3, static PHP 8.3 FPM + `proxy_fcgi`, or a new host. Do not in-place upgrade 16.04. Local code migration is not blocked. |
| MySQL 5.7.33 is end-of-life | Keep it staging-only and bound to `127.0.0.1`; plan a separate database upgrade after the PHP migration. |
| Composer reports ambiguous `ADD_Component_DeleteSectionButton` in `clinicle/core` and `clinicle/touch` | Files are byte-identical; the touch copy wins. Remove one copy later or add an `exclude-from-classmap` rule. |
| Removing the Composer installer changes how module code and web/view links reach `work/` | Capture the live `work/` layout and symlink targets; replicate with a build script; verify `ModuleSetup` output. `modules.ini` is not used. |
| `InstallCommand` still contains module code installation | Strip Composer/copy/symlink logic; keep only `installForms()`; test against an existing module in `work/` |
| `work/` module tree generation breaks after restructure | Compare module set, aliases, and web/view symlinks against baseline |
| Six deferred packages are absent from this checkout | Capture from live before Composer resolution; do not patch them yet |
| PhpSpreadsheet reads dates/types differently | Compare parsed rows for representative XLSX/XLS files |
| PHPWord 1.x changes DOCX output | Compare extracted text and placeholder substitution |
| TCPDF/FPDF output changes | Compare PDF text, page count, and barcode readability |
| Symfony 2.8 deprecation noise | Log only; do not upgrade in this project |
| PMI FreeTDS/TDS mismatch | Verify with `tsql` before cutover; capture live `freetds.conf` |
| Composer cannot reach Packagist inside the VPN | Build the artifact where Packagist is reachable; copy `vendor/` to staging |
| Production `config/` is not in git | Capture from live; commit templates without secrets |

## 7. Non-goals

- Upgrading CodeIgniter.
- In-place Ubuntu 16.04 → 18.04 → 20.04 → 22.04 → 24.04 upgrades. Use the fresh VM and keep the old host for rollback.
- Relocating `clinicle/web/public` or the CI `system/`/`application/` trees to a new root `public/` directory. A symlink is allowed; moving files is not part of this migration.
- Upgrading Symfony, Guzzle, Pimple, UUID, RandomLib, or json-schema.
- Rewriting module business logic.
- Installing module code via Composer; modules are deployed from `user/`/`work/`.
- Running the module installer; `clinicle/install` remains only for dictionary/form setup.
- Database schema changes.
- Porting the legacy PHPUnit suite.
- R/rspiro, Guzzle remote monitoring, or CiviCRM integration.
- Adding strict types, typed properties, or a PSR-12 reformat.
- Eliminating every PHP 8.2 deprecation; only fix those that are fatal or operationally
  noisy.

## 8. Audit commands

```bash
# lint + PHPCompatibility on first-party code
PHP_BIN=php8.3 tools/php82-audit.sh --first-party

# full tree, including third-party vendor
PHP_BIN=php8.3 tools/php82-audit.sh
```

The audit writes `summary.md`, `syntax.tsv`, `removed-api.tsv`, `deprecations.tsv`,
and `review.tsv` under `reports/php82-audit/` by default.
