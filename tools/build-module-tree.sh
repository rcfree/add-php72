#!/usr/bin/env bash
#
# Build the runtime module tree from user/ without the old Composer installer.
#
# Usage:
#   tools/build-module-tree.sh          # symlink packages into work/
#   tools/build-module-tree.sh --copy   # copy packages into work/
#
# Layout produced:
#   work/<vendor>/<package>                       symlink or copy
#   vendor/clinicle/web/public/module/<vendor>/<package>
#   vendor/clinicle/web/public/application/views/module/<vendor>/<package>
#
# Only packages with "type": "clinicle-module" are included.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORK="$ROOT/work"
WEB="$ROOT/vendor/clinicle/web/public"
MODE="symlink"

[ "${1:-}" = "--copy" ] && MODE="copy"

if [ ! -d "$ROOT/user" ]; then
    echo "ERROR: $ROOT/user does not exist" >&2
    exit 1
fi

rm -rf "$WORK"
mkdir -p "$WORK"

python3 - "$ROOT" <<'PY' > /tmp/add-modules.$$
import json, os, sys
root = sys.argv[1]
for vendor in sorted(os.listdir(os.path.join(root, 'user'))):
    vdir = os.path.join(root, 'user', vendor)
    if not os.path.isdir(vdir):
        continue
    for package in sorted(os.listdir(vdir)):
        pdir = os.path.join(vdir, package)
        cj = os.path.join(pdir, 'composer.json')
        if not os.path.isfile(cj):
            continue
        try:
            data = json.load(open(cj))
        except Exception:
            continue
        if data.get('type') != 'clinicle-module':
            continue
        name = data.get('name') or f"{vendor}/{package}"
        print(f"{name}\t{pdir}")
PY

while IFS=$'\t' read -r name src; do
    [ -n "$name" ] || continue
    target="$WORK/$name"
    mkdir -p "$(dirname "$target")"
    if [ "$MODE" = "copy" ]; then
        rsync -a --exclude='.git' "$src/" "$target/"
        link_src="$target"
    else
        ln -s "$src" "$target"
        link_src="$src"
    fi

    if [ -d "$src/web" ]; then
        mkdir -p "$WEB/module/$(dirname "$name")"
        ln -sfn "$link_src/web" "$WEB/module/$name"
    fi
    if [ -d "$src/views" ]; then
        mkdir -p "$WEB/application/views/module/$(dirname "$name")"
        ln -sfn "$link_src/views" "$WEB/application/views/module/$name"
    fi
done < /tmp/add-modules.$$

rm -f /tmp/add-modules.$$

module_count=$(find "$WORK" -mindepth 2 -maxdepth 2 \( -type l -o -type d \) | wc -l | tr -d ' ')
echo "Built $MODE module tree under $WORK ($module_count modules)"
