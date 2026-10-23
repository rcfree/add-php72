# ADD PHP 8.3 Migration Log

Append-only execution log. Decisions and scope live in `MIGRATION_PLAN.md`; this file
records what was actually done, where, and with what result. Add new entries at the
bottom. Do not rewrite history; correct an entry with a follow-up note.

## 2026-10-23

### Baseline audit (host checkout)

- Source: `/local/add`.
- Live runtime reported: PHP 5.6, Ubuntu 16.04, CodeIgniter 2.1.3.
- `tools/php82-audit.sh` against PHP 8.3.6:
  - 5,066 PHP files total; 1,527 first-party.
  - `modules/`: 710 files, 0 syntax errors.
  - 44 non-test syntax blockers: 4 first-party framework, 21 PHPExcel, 12 PHPWord,
    7 TCPDF.
  - 168 removed-API hits, mostly unused CI database drivers and CI Xmlrpc/Encrypt.
  - `phpcbf` trial on copies of PHPExcel and TCPDF: 293 fixes in 26 files, 0 remaining
    syntax failures.
- Runtime smoke tests:
  - Passed: Symfony 2.8 event-dispatcher/http-foundation/filesystem/finder/console,
    Pimple, log4php, Minify JS, UUID, RandomLib, JSON Schema, Wrep class load,
    Guzzle 3 class load.
  - Failed: FPDF constructor (`get_magic_quotes_runtime`), Minify CSS
    (`implode(array_keys(...), '|')` at `CSS.php:528`).
- `modules.ini` confirmed unused at runtime; only `ComposerModuleManager` reads or
  writes it.

### VM provisioning

- Host: Linux Mint 22.3, Lenovo i7-1185G7. VT-x was initially disabled; enabled in
  BIOS. `/dev/kvm` now exists and QEMU uses `accel=kvm`.
- QEMU 8.2.2 extracted locally to `/local/vm/qemu` because the host has no system
  QEMU and sudo requires a password.
- Base image: `noble-server-cloudimg-amd64.img` (Ubuntu 24.04.5), SHA256 verified.
- VM files: `/local/vm/add-php83`.
  - `add-php83.qcow2`: 60 GB overlay.
  - `data.qcow2`: 100 GB data volume.
  - `seed.iso`: cloud-init NoCloud seed.
  - `id_ed25519`: VM SSH key.
- Access:
  ```bash
  /local/vm/add-php83/start.sh
  /local/vm/add-php83/stop.sh
  ssh -i /local/vm/add-php83/id_ed25519 -p 2222 rob@127.0.0.1
  ```
- Boot-order issue: the data disk was enumerated first by BIOS. Fixed by using
  explicit `virtio-blk-pci` devices with `bootindex=1` on the root disk.
- Cloud-init installed the user and SSH key but skipped its package module on the
  second boot (`once-per-instance`). Packages were installed manually over SSH.
  `cloud-init status` still reports the `scripts_user` error; the stack is unaffected.
- Installed and verified:
  - PHP 8.3.6, Apache 2.4.58, Composer 2.10.3.
  - Extensions: `pdo_mysql`, `mysqli`, `pdo_dblib`, `mbstring`, `xml`, `zip`, `gd`,
    `curl`, `opcache`.
- Storage:
  - `/mnt/host_add`: read-only 9p share of host `/local/add`
    (`-virtfs local,path=/local/add,mount_tag=host_add,security_model=none,readonly=on`).
  - `/srv/add-data`: ext4 on `/dev/disk/by-id/virtio-ADDDATA`.
  - `/local/reports` -> `/srv/add-data/reports`.
  - `/media/uhl_data` -> `/srv/add-data/uhl_data`.
  - Persistent mounts added to `/etc/fstab` with `nofail`.
- The host checkout was seeded to `/local/add` on the VM with `rsync`, excluding
  `backup`, `log`, `tmp`, `search`, `reports`, and `.hg`.
- `tools/php82-audit.sh --quick --first-party` on the VM reproduces the same
  4 first-party syntax blockers as the host.

### Database and PHP extensions

- No database server was installed initially; only `php8.3-mysql`.
- The SQL previews identify the source server as **MariaDB 10.0.38** (Ubuntu 16.04).
  Installed **MariaDB 10.11.14** rather than MySQL 8.0 to reduce legacy SQL and
  reserved-word risk (`group`, `groups`).
- MariaDB is active and listening on `127.0.0.1:3306`.
- Created:
  - `add_data` (`utf8`/`utf8_general_ci`), preview imported: 35 tables.
  - `add_user` (`utf8`/`utf8_general_ci`), preview imported: 9 tables.
  - Application user `add`@`localhost` with password `add`, granted on both databases.
- PHP extension repair:
  - `php8.3-mysql` was installed but `php -m` showed no `PDO`, `mysqli`, or
    `pdo_mysql`.
  - Cause: the `mods-available/*.ini` files were zero bytes; the package post-install
    `ucf` step had not copied the templates.
  - Fixed by copying `*.ini` from `/usr/share/php8.3-*` into
    `/etc/php/8.3/mods-available/` and restarting Apache.
  - Verified loaded: `PDO`, `pdo_mysql`, `mysqli`, `mysqlnd`, `pdo_dblib`, `gd`,
    `curl`, `mbstring`, `xml`, `zip`, `opcache`.
- Connectivity verified with `mariadb -u add -padd add_data`; PHP PDO class check
  returns true.

### Production DB version check (open)

- On the production VM, `mysql --version` reports
  `Ver 14.14 Distrib 5.7.33`. That is the client version, not the server.
- The SQL dumps report `10.0.38-MariaDB`; the two observations may refer to
  different hosts or the dumps may predate production.
- Required before importing the full dump:
  ```bash
  mysql -h <db-host> -u <db-user> -p \
    -e "SELECT VERSION(), @@version_comment;"
  ```
  or `mysqladmin ... version`.
- The MariaDB 10.11 install on the VM is provisional. If the server is MySQL 5.7,
  switch the VM to MySQL 8.0 (or a MySQL 5.7 parity instance) before loading the
  full database.
- `MIGRATION_PLAN.md` records this as an open risk and marks the DB decision
  provisional.

### Correction: production DB is MySQL 5.7.33

- Clarified that the preview dumps come from a different MariaDB 10.0.38 server
  that mirrors the live database; production itself is **MySQL 5.7.33**.
- Removed the provisional MariaDB 10.11 installation.
- Installed Docker and started the exact server version:
  ```bash
  sudo docker run -d --name add-mysql --restart unless-stopped \
    -p 127.0.0.1:3306:3306 \
    -v /srv/add-data/mysql:/var/lib/mysql \
    -e MYSQL_ROOT_PASSWORD=addroot \
    mysql:5.7.33 \
    --character-set-server=utf8mb4 --collation-server=utf8mb4_general_ci
  ```
- Verified:
  - `SELECT VERSION()` returns `5.7.33`; `@@version_comment` is
    `MySQL Community Server (GPL)`.
  - Recreated `add_data` (35 tables) and `add_user` (9 tables) with the preview
    dumps.
  - `add`@`%` user created and granted on both databases.
  - PHP PDO from the VM returns server `5.7.33` and sees 35 tables in `add_data`.
- The preview dumps are a schema smoke test only. A full production dump from the
  MySQL 5.7.33 server is still required before final validation.

### Data handling constraint

- Production data must not leave the VPN. No full production dump will be copied to
  the local host or VM.
- The local MySQL 5.7.33 container uses the preview dumps only. They are a schema and
  smoke-test fixture, not production data.
- Local validation is limited to preview data and code-level checks. Final HTTP,
  output, and database validation runs in the VPN after the code is ported.
- Do not add production dumps, patient data, or `/media/uhl_data` to git or to the
  local VM.
- This supersedes the earlier “full production dump still required” note: the full
  dump remains inside the VPN and is used only for final validation there.

### Decision: no in-place OS upgrade

- The 16.04 host will not be upgraded through 18.04 → 20.04 → 22.04 → 24.04.
  That path mixes OS, PHP 5.6 removal, database, Apache, and app changes in one
  hard-to-rollback sequence.
- The fresh Ubuntu 24.04 VM is the migration target; the 16.04 host remains the
  rollback environment until cutover is signed off.
- `MIGRATION_PLAN.md` records this as a locked decision and a non-goal.

### Open item: target VM re-provisioning permission

- The target VM may not be re-provisionable due to permissions. The user will resolve
  this separately.
- No in-place 16.04 → 24.04 upgrade will be performed.
- Fallback strategies if a new VM is not allowed: containerized PHP 8.3 on the existing
  host, static PHP 8.3 FPM with Apache `proxy_fcgi`, or a new host/cloud instance.
- This does not block local code migration or validation with preview data. The final
  deployment target remains to be confirmed.

### Phase 1 restructure completed (local)

- Rollback archive: `/local/backups/add-pre-restructure-20261023.tgz` (8.4 MB,
  first-party source, composer files, config; excludes VCS history and nested vendors).
- Root git monorepo initialised:
  - `1f82d8a` baseline before restructure;
  - `435cf68` moved first-party packages to `user/` and added Composer path repositories.
- Removed `.hg`/`.git` metadata, `.hgtags`, `.hgignore`, nested module vendors, and
  stale module locks.
- Moved:
  - `modules/add`, `modules/clinicle`, `modules/cdbrkr`, `modules/ember`;
  - `modules/main2` -> `user/copd/main`;
  - `vendor/clinicle/*` -> `user/clinicle/*`;
  - `vendor/leicrespbru/lrbtools` -> `user/leicrespbru/lrbtools`.
- Copied the six previously missing packages from `/local/add_modules` into the source
  tree, then moved them with the rest. Versions for all 73 path packages are pinned in
  `composer.json` from `composer.lock`.
- Fixed `user/add/research/composer.json` (`tenickcom` -> `tecnickcom`).
- Converted `user/clinicle/install` from a Composer plugin to a library:
  - removed `composer-plugin-api`, `composer/composer`, plugin metadata, and scripts;
  - deleted `ComposerModuleInstaller*` and `ComposerModuleManager`.
- Composer 2 resolution/install:
  - removed only Composer tooling packages (`composer/*`, `seld/*`,
    `justinrainbow/json-schema`) from the lock;
  - no runtime third-party versions changed;
  - installed 105 packages from the new lock.
- Added `tools/build-module-tree.sh`:
  - builds `work/<vendor>/<package>` from `user/` using module `composer.json` type;
  - creates web/view symlinks under `vendor/clinicle/web/public`;
  - produced 60 module links.
- Fixed CLI boot on PHP 8.3:
  - `ClinicLE\Command\CommandRunner` and `ClinicLE\Manage\Runner\CommandRunner`
    converted dynamic-property deprecations into exceptions; both now ignore
    `E_DEPRECATED`/`E_USER_DEPRECATED`.
  - Created VM runtime dirs (`/local/tmp`, `log`, `tmp`, `cache`, `search`) after the
    log4php config failed to `mkdir` under a root-owned `/local`.
  - `ADD_PATH=/local/add ADD_PROFILE=uhl-test php vendor/bin/clinicle list` succeeds
    on the VM and lists the expected commands.
- Composer reports one ambiguous class:
  `ADD_Component_DeleteSectionButton` exists identically in `clinicle/core` and
  `clinicle/touch`; the touch copy wins. Files are byte-identical, so this is currently
  harmless.
- `InstallCommand` remains unregistered and still contains module-install code; its
  forms-only reduction is deferred to Phase 4.

### Phase 3 dependency migration completed

- Minify 1.3.61 -> 1.3.75; CSS and JS minification both pass.
- TCPDF 6.2.26 -> 6.11.4; a C39 barcode PDF (\~6.9 KB) generated successfully on
  the VM. TCPDF 6.11 requires the curl extension at class load; the VM has it.
- FPDF `itbz/fpdf` 1.7.2 -> `setasign/fpdf` 1.9.0.
  - `fpdf/fpdf` was rejected: it is a Laravel wrapper exposing `Fpdf\Fpdf`, not the
    global `FPDF` class.
  - `ADD_Tool_PDF` now extends `\FPDF`; plain FPDF and `ADD_Tool_PDF` both produce
    valid one-page PDFs.
- PHPWord 0.11.1 -> 1.4.0.
  - `ADD_Tool_RTF` uses `PhpOffice\PhpWord\TemplateProcessor`.
  - DOCX template substitution produced `Hello World` with a valid DOCX.
- PHPExcel 1.8.1 -> PhpSpreadsheet 5.10.0.
  - `ExcelDataFile` reads the sample XLSX: 16 rows, 14 headings.
  - `LRB_DataFile_Excel` reads the sample XLSX: 79 rows.
  - Removed `phpoffice/phpexcel`; added PhpSpreadsheet and its five dependencies.
- No pinned third-party runtime package moved during these updates. Only the
  previously removed Composer tooling packages are absent.

### Phase 4 core patches applied

- Fixed the four first-party syntax blockers:
  - `Html.php`, `HtmlElement.php`: parenthesised nested ternaries.
  - `Log.php`: rewrote lossy debug-output expression.
  - `Profiler.php`: corrected dynamic property access
    (`$this->{'_compile_'.$section}`), not the phpcbf array rewrite.
- CI 2.1.3:
  - `Security::xss_clean` `each()` -> `foreach`.
  - `Security::entity_decode` `/e` -> `preg_replace_callback`.
  - removed the `mysql_escape_string` fallback from the mysqli driver.
  - profile CI DB config switched to `mysqli` and `mysqli_report(MYSQLI_REPORT_OFF)`.
- Removed `$php_errormsg` and `track_errors` references.
- Added `patches/log4php-2.3.0-each.patch` and an idempotent `tools/apply-patches.sh`;
  applied and re-run to confirm idempotency.
- `tools/php82-audit.sh --quick --first-party`: **0 syntax blockers**.
- CLI boot on the VM still succeeds and lists all commands.

### Web/CI smoke and final local fixes

- CI `Security` smoke:
  - `entity_decode('&#65;&#x42;')` -> `AB` (both `/e` replacements);
  - `xss_clean('<script>alert(1)</script>')` sanitized;
  - array input path (`each` -> `foreach`) returns sanitized array.
- Direct mysqli connection to the VM's MySQL 5.7.33 container:
  - `mysqli` OK, 35 tables in `add_data`, server `5.7.33`.
- Local profile `config/local` (untracked) points at `127.0.0.1`:
  - `add_data` via PDO, `add_user` via CI mysqli, `MockPMI`.
- PHP built-in server on the VM served the CI app:
  - `GET /` -> HTTP 200, title `ADD [local] - Home`, no error panel;
  - Minifier assets resolve after rebuilding the module tree.
- Runtime fixes found by the web smoke:
  - `CI_Input::valid_ip()` passed `''` as `filter_var` options; changed to `0`.
  - `web_init.php` had a closing `?>` that emitted output and broke session
    headers; removed.
  - `ClinicLE\Common\ErrorHandler` and `ADD_Tool_ErrorHandler` shutdown handlers
    treated any last error (including deprecations) as fatal; restricted to real
    fatal types and ignored `E_USER_DEPRECATED`.
  - CI front controller now defaults to `production`, overridable with
    `ADD_ENVIRONMENT`.
- `InstallCommand` reduced to forms/dictionary setup:
  - no Composer require/update, copy, symlink, backup, or dump-autoload;
  - `ModuleInstall::installCode/copyModule/removeExistingModule/createSymLinks`
    removed;
  - no Composer library references remain in `clinicle/install`.
- Static audit still reports **0 syntax blockers**; CLI `list` still succeeds.

### Version lookup cleanup

- `AbstractEnvironment::getSystemVersion()` now reads `system_version` from the
  profile (falling back to `version`), instead of shelling out to Mercurial.
- `Module::version()` now reads the module's `VERSION` file, then `composer.json`
  `version`, returning `[version, date]`; no Mercurial or `hg_binary` dependency.
- Removed the last `hg_binary` references from first-party code.
- Local profile sets `system_version=2.8.0`.
- Verified:
  - `getSystemVersion()` returns `2.8.0` on the VM;
  - web home still returns HTTP 200 with title `ADD [local] - Home`;
  - CLI `list` still succeeds.

### Preview admin and authenticated smoke

- Seeded the preview `add_user` database:
  - groups `admin` (1104) and `members` (1105);
  - user `admin` (id 175), active, `last_changed` set to now;
  - password stored in ion_auth's configured sha1/store_salt=false format;
  - user linked to the `admin` group.
- Credentials for the local preview only: `admin` / `PreviewAdmin123`.
- MySQL 5.7's default `ONLY_FULL_GROUP_BY` broke the legacy recent-patients
  query (`DISTINCT ... ORDER BY`). Restarted the container with
  `--sql-mode=NO_ENGINE_SUBSTITUTION`; the query now succeeds. Production
  `@@sql_mode` must still be captured and matched.
- Verified:
  - `auth/loggedin` returns `{"logged_in":true}`;
  - browser login shows the authenticated header with `Options` and `Logout`;
  - home returns HTTP 200 without an error panel.
- Added local-only login helper at `http://127.0.0.1:8081/preview-login.html`
  (untracked, not part of the application build).

### Root login form (local preview)

- `tools/php-builtin-router.php` now serves `tools/preview-login.html` at `/`
  when no `ci_session` cookie is present. After login, `/` is handed to the CI app.
- `tools/preview-login.html` is tracked with the preview credentials; it is local-only
  and not part of the application build.
- Verified:
  - curl without a cookie at `/` returns the login form;
  - curl with a valid session at `/` returns the authenticated app;
  - browser login from `/` switches to the authenticated header (`Options`/`Logout`).
- If the browser already has a session, `/` goes straight to the app. Use `Logout`
  or a private window to see the form again.
- Added `/login` and `/?login=1`, which always serve the form even when a session
  cookie exists; responses are `Cache-Control: no-store`.

### Login simplification (supersedes the router login hacks)

- The pre-restructure archive has the same `Auth::login` redirect to `/` and the
  same absence of an auth gate in `Content::index`. Those two files were not
  changed by the PHP 8 port, so the blank unauthenticated root is not a PHP 8
  regression in them.
- The login view itself was already complete: `auth/login.php` includes
  `header.php` and `footer.php`.
- Two app-level fixes replace the router workaround:
  - `Auth::login` loads `auth/login` instead of redirecting when it has no POST.
  - `Content::index` redirects unauthenticated users to `auth/login`.
- `tools/php-builtin-router.php` is back to static-file serving plus CI handoff;
  no login-specific paths.
- Removed the standalone `tools/preview-login.html` and its docroot copy.
- Verified:
  - browser `/` follows CI's `Refresh` header to `/auth/login`;
  - login page has the ADD navbar, form, and footer;
  - successful login returns to `/` with `Options`/`Logout`;
  - failed login returns to the branded login page.
- If production previously showed a login at root, the remaining difference is
  outside these files (for example an Apache/vhost redirect or a different route);
  capture that config in the VPN phase before changing behavior again.

### Scope correction: login UX reverted

- Reverted the login/root changes in `Auth::login`, `Content::index`, and
  `tools/php-builtin-router.php`. They were UX changes, not PHP 8.2 compatibility
  fixes.
- Removed the standalone `tools/preview-login.html` and its docroot copy.
- The pre-restructure archive has the same redirect behavior, so the blank
  unauthenticated root is legacy. There is no PHP 8.2 change to revert for it.
- Direct POST to `/index.php/auth/login` still authenticates; the seeded preview
  admin remains usable.
- If production previously rendered a login at root, the difference is in
  deployment config (Apache/vhost/route), not in these controllers. Capture that
  during the VPN phase before changing behavior.

### Apache rewrite verification

- Installed a preview vhost on VM port 8082 with the CI public docroot,
  `AllowOverride All`, `mod_rewrite`, and `ADD_PATH`/`ADD_PROFILE` env.
- CI's log class chmods `application/logs`; the preview Apache workers run as
  `rob` and the writable dirs are `rob:rob` (`application/logs`, `log`, `tmp`,
  `cache`). Running Apache as `www-data` fails the chmod.
- Under Apache 2.4 + mod_php 8.3:
  - `/` returns HTTP 200, 2,844 bytes, title `ADD [local] - Home` (legacy shell);
  - `/auth/login` returns `Refresh: 0;url=/`.
- No file under `user/` or `vendor/` calls `load->view('auth/login')`; the view
  exists but is unreachable in this codebase.
- Conclusion: the missing login form is not caused by the PHP built-in server,
  the Apache rewrite, or PHP 8.2. Production likely differed in deployed code,
  an external login page, or Apache-level auth/redirect config. Capture that
  before changing application behavior.

### Legacy module loader restored

- The legacy `ADD_Manage_Load` command was failing on a missing
  `ADD_Manage_Install` parent class.
- Added `user/clinicle/core/lib/ADD/Manage/Install.php` as a compatibility
  adapter. It:
  - loads the module from `work/` via `SingleModuleSetup`;
  - with `-r`, only verifies/reports the existing module and exits;
  - otherwise calls `ModuleInstall::installForms()`;
  - never copies, downloads, symlinks, or removes module code.
- `-n` maps to the existing dry-run mode; `-p` maps to delete-missing forms.
- The adapter implements `runCommand()` because `ADD_Manage_Interface` is also
  absent from this checkout.
- `ADD_Manage_Load::getOptsList()` now exposes `r`.
- Also added `-r` to the namespaced `InstallCommand`, which is what
  `clinicle install` actually resolves to. It returns immediately after loading
  the module, before forms/dictionaries.
- Verified on the VM:

```text
$ clinicle install -r clinicle/options
Module 'clinicle/options' loaded from /local/add/work/clinicle/options
STATE:SUCCESS

$ clinicle load -r clinicle/options
Module 'clinicle/options' loaded from /local/add/work/clinicle/options
STATE:SUCCESS
```

- This does not create a DB module row; this schema has no module registry table.
  The system loads modules from the filesystem.
- It also does not fix the blank home page: the login tool is returned by
  `/client/panel-content`, but the view/client panel IDs do not match.

### Next action

- Local work is complete. Remaining work is VPN-only:
  - capture live `work/` layout, aliases, config, FreeTDS, and row counts;
  - add `system_version` (and the equivalent module version metadata) to the
    production profile;
  - port the code, run the production-data smoke matrix, and compare outputs;
  - decide the deployment path if a fresh VM is not permitted.
- Keep the local VM on preview data; no production data outside the VPN.
