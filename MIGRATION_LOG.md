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

### Next action

- Phase 0 capture on the live/staging host, inside the VPN:
  - PHP modules, Apache modules, `/local/add/config/<profile>/`;
  - MySQL server variables, `sql_mode`, schema-only metadata, and table counts;
    **not** production rows;
  - `work/` tree, web/view symlink targets, and `page.ini` aliases;
  - the six missing packages (`add/inv`, `add/med`, `clinicle/search`,
    `ember/asthcopd`, `ember/blness`, `ember/pneumonia`) and their versions;
  - cron/systemd units and PMI FreeTDS settings.
- Phase 1 restructure can then proceed locally with the preview database; the six
  packages are required for Composer path-repository resolution.
- Final validation is run in the VPN after porting the code; no production data is
  imported locally.
