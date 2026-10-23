#!/usr/bin/env bash
#
# php82-audit.sh - repeatable PHP 8.2 compatibility audit for ADD.
#
# Usage:
#   tools/php82-audit.sh                 # lint + PHPCompatibility, all code
#   tools/php82-audit.sh --quick         # lint only
#   tools/php82-audit.sh --first-party   # only code we maintain
#   tools/php82-audit.sh --install-deps  # install PHPCS + PHPCompatibility
#
# Environment:
#   PHP_BIN    PHP 8.2+ CLI (default: php)
#   PHPCS_BIN  phpcs executable (default: tools/.audit/vendor/bin/phpcs,
#              then phpcs on PATH)
#   JOBS       parallel lint jobs (default: 4)
#   OUT        report directory (default: reports/php82-audit)
#
# Exit codes: 0 = no syntax blockers, 1 = syntax blockers, 2 = setup error.
#
# Classification:
#   SYNTAX      file will not compile on PHP 8.2 (hard blocker)
#   REMOVED_API removed function/extension/constant; reachability must be
#               checked because PHPCS cannot see runtime guards
#   DEPRECATION works on 8.2, will break on a later PHP
#   REVIEW      behaviour change or false positive worth a human look

set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT="${OUT:-$ROOT/reports/php82-audit}"
PHP_BIN="${PHP_BIN:-php}"
JOBS="${JOBS:-4}"
QUICK=0
FIRST_PARTY=0
INSTALL_DEPS=0

for arg in "$@"; do
    case "$arg" in
        --quick) QUICK=1 ;;
        --first-party) FIRST_PARTY=1 ;;
        --install-deps) INSTALL_DEPS=1 ;;
        -h|--help) sed -n '2,35p' "$0"; exit 0 ;;
        *) echo "Unknown option: $arg" >&2; exit 2 ;;
    esac
done

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
    echo "ERROR: PHP binary not found: $PHP_BIN" >&2
    exit 2
fi

PHP_VERSION="$("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null | tr -d '\r')"
if ! "$PHP_BIN" -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);' 2>/dev/null; then
    echo "ERROR: need PHP >= 8.2 for this audit; $PHP_BIN is ${PHP_VERSION:-unknown}" >&2
    exit 2
fi

mkdir -p "$OUT"
: > "$OUT/lint.tsv"
: > "$OUT/phpcs.json"
: > "$OUT/phpcs.err"

cd "$ROOT" || exit 2

if [ "$FIRST_PARTY" -eq 1 ]; then
    TARGETS=(user bin config)
else
    TARGETS=(user vendor bin config)
fi

# Generated first-party mirrors live in vendor/ after composer install; scan
# the source under user/ instead. Build copies, logs and nested vendors are
# not runtime code.
PRUNE=(
    -name .hg -prune -o
    -name backup -prune -o
    -name reports -prune -o
    -name tmp -prune -o
    -name log -prune -o
    -name .audit -prune -o
    -path 'user/*/*/vendor' -prune -o
    -path 'vendor/add' -prune -o
    -path 'vendor/cdbrkr' -prune -o
    -path 'vendor/clinicle' -prune -o
    -path 'vendor/copd' -prune -o
    -path 'vendor/ember' -prune -o
    -path 'vendor/leicrespbru' -prune -o
    -name '*.php' -print0
)

echo "PHP $PHP_VERSION; scanning: ${TARGETS[*]}"

# --- 1. Syntax check -------------------------------------------------------
# Use the exit status: PHP 8 emits "Fatal error: ... Errors parsing ..." for
# several compile failures, not "Parse error".
lint_one() {
    local f="$1" out rc
    out="$("$PHP_BIN" -l "$f" 2>&1)"
    rc=$?
    if [ "$rc" -ne 0 ]; then
        printf '%s\t%s\n' "$f" "$(printf '%s' "$out" | grep -m1 -E 'Fatal error|Parse error' | tr -d '\r')"
    fi
}
export -f lint_one
export PHP_BIN

echo "Linting..."
find "${TARGETS[@]}" \( "${PRUNE[@]}" \) \
    | xargs -0 -r -P "$JOBS" -n 1 bash -c 'lint_one "$1"' _ \
    | sort > "$OUT/lint.tsv"

LINT_COUNT=$(wc -l < "$OUT/lint.tsv" | tr -d ' ')
echo "  syntax failures: $LINT_COUNT"

# --- 2. PHPCompatibility ---------------------------------------------------
PHPCS_BIN="${PHPCS_BIN:-}"
if [ -z "$PHPCS_BIN" ]; then
    if [ -x "$ROOT/tools/.audit/vendor/bin/phpcs" ]; then
        PHPCS_BIN="$ROOT/tools/.audit/vendor/bin/phpcs"
    elif command -v phpcs >/dev/null 2>&1; then
        PHPCS_BIN="$(command -v phpcs)"
    fi
fi

if [ "$QUICK" -eq 0 ] && [ -z "$PHPCS_BIN" ] && [ "$INSTALL_DEPS" -eq 1 ]; then
    echo "Installing PHPCS + PHPCompatibility into tools/.audit ..."
    mkdir -p "$ROOT/tools/.audit"
    (
        cd "$ROOT/tools/.audit" || exit 1
        [ -f composer.json ] || cat > composer.json <<'JSON'
{"require":{"squizlabs/php_codesniffer":"^3.10","phpcompatibility/php-compatibility":"^9.3"},"config":{"allow-plugins":true}}
JSON
        composer install --no-interaction --no-progress
    ) || exit 2
    PHPCS_BIN="$ROOT/tools/.audit/vendor/bin/phpcs"
fi

if [ "$QUICK" -eq 0 ] && [ -n "$PHPCS_BIN" ] && [ -x "$PHPCS_BIN" ]; then
    PHPCS_STD="$ROOT/tools/.audit/vendor/phpcompatibility/php-compatibility"
    [ -d "$PHPCS_STD" ] || PHPCS_STD=""
    echo "Running PHPCompatibility (testVersion 8.2)..."
    ARGS=(--standard=PHPCompatibility --runtime-set testVersion 8.2
          --extensions=php -s --report=json
          --ignore='*/test/*,*/tests/*,*/unitTests/*,*/examples/*,*/Documentation/*,*/Fixtures/*')
    [ -n "$PHPCS_STD" ] && ARGS+=(--runtime-set installed_paths "$PHPCS_STD")
    "$PHPCS_BIN" "${ARGS[@]}" "${TARGETS[@]}" > "$OUT/phpcs.json" 2> "$OUT/phpcs.err"
    echo "  phpcs exit: $? (1/2 = findings, not a setup failure)"
elif [ "$QUICK" -eq 0 ]; then
    echo "WARNING: phpcs not found; run with --install-deps or set PHPCS_BIN." >&2
fi

# --- 3. Classify -----------------------------------------------------------
python3 - "$ROOT" "$OUT" "$PHP_VERSION" <<'PY'
import json, os, re, sys, collections, datetime

root, out, phpver = sys.argv[1], sys.argv[2], sys.argv[3]
SYNTAX, REMOVED, DEPRECATION, REVIEW = 'SYNTAX', 'REMOVED_API', 'DEPRECATION', 'REVIEW'

def is_test(path):
    return bool(re.search(r'/(test|tests|unitTests|examples|Documentation|Fixtures)/', path))

def severity(source):
    if 'Internal.LineEndings' in source:
        return None
    if ('RemovedTernaryAssociativity' in source
            or 'RemovedCurlyBraceArrayAccess' in source):
        return SYNTAX
    if ('RemovedFunctions' in source or 'RemovedExtensions' in source
            or 'RemovedConstants' in source):
        return REMOVED
    if ('Deprecated' in source or 'RemovedIniDirectives' in source
            or 'NewPasswordAlgoConstantValues' in source
            or 'RemovedImplodeFlexibleParamOrder' in source):
        return DEPRECATION
    return REVIEW

lint = []
with open(os.path.join(out, 'lint.tsv')) as fh:
    for line in fh:
        parts = line.rstrip('\n').split('\t', 1)
        if len(parts) == 2:
            lint.append((parts[0], parts[1]))

findings = []
raw = open(os.path.join(out, 'phpcs.json')).read()
data = {}
if '{"totals"' in raw:
    try:
        data = json.loads(raw[raw.find('{"totals"'):])
    except Exception:
        pass

for path, info in data.get('files', {}).items():
    rel = os.path.relpath(path, root)
    for m in info.get('messages', []):
        sev = severity(m['source'])
        if sev:
            findings.append((sev, rel, m['line'], m['source'], m.get('message', '')))

syntax_files = {}
for path, msg in lint:
    syntax_files[path] = msg

# Drop PHPCS duplicates for files that already failed to compile.
findings = [f for f in findings if not (f[0] == SYNTAX and f[1] in syntax_files)]
for path, msg in sorted(syntax_files.items()):
    findings.append((SYNTAX, path, 0, 'php8.syntax', msg.strip()))

rows = [f for f in findings if not is_test(f[1])]
syntax = [f for f in rows if f[0] == SYNTAX]
removed = [f for f in rows if f[0] == REMOVED]
deprecated = [f for f in rows if f[0] == DEPRECATION]
review = [f for f in rows if f[0] == REVIEW]

def write_list(name, items):
    with open(os.path.join(out, name), 'w') as fh:
        for sev, path, line, src, msg in sorted(items, key=lambda r: (r[1], r[2])):
            fh.write(f'{sev}\t{path}:{line}\t{src}\t{msg}\n')

write_list('syntax.tsv', syntax)
write_list('removed-api.tsv', removed)
write_list('deprecations.tsv', deprecated)
write_list('review.tsv', review)

def group(items):
    c = collections.Counter()
    for _, path, _, src, _ in items:
        if path.startswith('vendor/'):
            key = '/'.join(path.split('/')[:3])
        else:
            key = '/'.join(path.split('/')[:2])
        c[(key, src.split('.')[2] if src.count('.') > 2 else src)] += 1
    return c

summary = os.path.join(out, 'summary.md')
with open(summary, 'w') as fh:
    fh.write('# PHP 8.2 compatibility audit\n\n')
    fh.write(f'- Date: {datetime.date.today().isoformat()}\n')
    fh.write(f'- PHP used for audit: {phpver}\n')
    fh.write(f'- Syntax blockers: {len(syntax)} files\n')
    fh.write(f'- Removed APIs (verify reachability): {len(removed)}\n')
    fh.write(f'- Deprecations: {len(deprecated)}\n')
    fh.write(f'- Review items: {len(review)}\n\n')
    fh.write('## Syntax blockers\n\n')
    for _, path, line, src, msg in sorted(syntax, key=lambda r: r[1]):
        fh.write(f'- `{path}` — {msg[:220]}\n')
    fh.write('\n## Removed APIs by area\n\n')
    for (key, src), n in group(removed).most_common(30):
        fh.write(f'- {n:3d} × {src} in `{key}`\n')
    fh.write('\n## Removed API detail\n\n')
    for _, path, line, src, msg in sorted(removed, key=lambda r: (r[1], r[2])):
        fh.write(f'- `{path}:{line}` — {msg[:180]}\n')
    fh.write('\n## Deprecations (top 60)\n\n')
    for _, path, line, src, msg in sorted(deprecated, key=lambda r: (r[1], r[2]))[:60]:
        fh.write(f'- `{path}:{line}` — {src}\n')

print(f'  syntax blockers: {len(syntax)} | removed APIs: {len(removed)} | '
      f'deprecations: {len(deprecated)} | review: {len(review)}')
print(f'  report: {summary}')
sys.exit(1 if syntax else 0)
PY
STATUS=$?

echo
echo "Reports:"
echo "  $OUT/summary.md"
echo "  $OUT/syntax.tsv"
echo "  $OUT/removed-api.tsv"
echo "  $OUT/deprecations.tsv"
echo "  $OUT/review.tsv"
exit "$STATUS"
