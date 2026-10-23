# Patch artifacts

The authoritative code is the current `user/` tree. These files are review
artifacts only; do not apply them on top of the current tree.

## Applied automatically

`log4php-2.3.0-each.patch` is applied by `tools/apply-patches.sh` after
Composer install/update. It replaces the removed `each()` loop in log4php.

## Review artifacts

These capture the first-party adapter changes for the third-party library
replacements. They are generated from git history and are not part of the
runtime build.

- `third-party-adapters.patch` — combined diff from `82384cf` to `1b52727`.
- `phpspreadsheet-adapters.patch` — PHPExcel → PhpSpreadsheet adapters and
  package requirements.
- `phpword-1.4-adapter.patch` — PHPWord 1.4 `TemplateProcessor` adapter and
  package requirement.
- `setasign-fpdf-adapter.patch` — `itbz/fpdf` → `setasign/fpdf` adapter and
  package requirement.

The per-library patches are commit-relative:

| Patch | Generated from parent of |
|---|---|
| `setasign-fpdf-adapter.patch` | `ad0bd6d` |
| `phpword-1.4-adapter.patch` | `d8ca200` |
| `phpspreadsheet-adapters.patch` | `1b52727` |

None of the adapter patches touch `composer.lock` or `vendor/`.
