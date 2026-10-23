#!/usr/bin/env bash
#
# Apply tracked third-party patches after composer install/update.
# Idempotent: already-applied patches are reported and skipped.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

shopt -s nullglob
for patch_file in patches/*.patch; do
    if patch -p1 --dry-run --forward < "$patch_file" >/dev/null 2>&1; then
        echo "Applying $patch_file"
        patch -p1 --forward < "$patch_file"
    elif patch -p1 --dry-run --reverse < "$patch_file" >/dev/null 2>&1; then
        echo "Already applied: $patch_file"
    else
        echo "ERROR: cannot apply $patch_file; vendor tree may have changed." >&2
        exit 1
    fi
done
