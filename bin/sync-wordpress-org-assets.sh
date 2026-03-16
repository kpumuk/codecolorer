#!/usr/bin/env bash

set -euo pipefail

usage() {
    cat <<'EOF'
Usage: bin/sync-wordpress-org-assets.sh

Environment:
  DRY_RUN=1            Validate and stage the SVN changes without committing.
  SVN_URL=...          Override the WordPress.org SVN URL.
  WPORG_USERNAME=...   WordPress.org SVN username. Required unless DRY_RUN=1.
  WPORG_PASSWORD=...   WordPress.org SVN password. Required unless DRY_RUN=1.
EOF
}

fail() {
    echo "Error: $*" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 || fail "Missing required command: $1"
}

require_path() {
    local path="$1"
    local kind="$2"

    if [[ "$kind" == "file" ]]; then
        [[ -f "$path" ]] || fail "Missing $path"
        return
    fi

    [[ -d "$path" ]] || fail "Missing $path"
}

svn_status_sync() {
    local svn_root="$1"
    local line status path

    while IFS= read -r line; do
        [[ -z "$line" ]] && continue
        status="${line:0:1}"
        path="${line:8}"

        case "$status" in
            '!')
                svn rm --force "$path" >/dev/null
                ;;
            '?')
                svn add --force "$path" >/dev/null
                ;;
        esac
    done < <(svn status "$svn_root")
}

if [[ $# -ne 0 ]]; then
    usage
    exit 1
fi

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SVN_URL="${SVN_URL:-https://plugins.svn.wordpress.org/codecolorer}"
DRY_RUN="${DRY_RUN:-0}"
WPORG_USERNAME="${WPORG_USERNAME:-}"
WPORG_PASSWORD="${WPORG_PASSWORD:-}"
ASSETS_DIR="$ROOT/.wordpress-org/assets"

require_command rsync
require_command svn

require_path "$ASSETS_DIR" dir

if [[ "$DRY_RUN" != "1" ]]; then
    [[ -n "$WPORG_USERNAME" ]] || fail "WPORG_USERNAME is required unless DRY_RUN=1"
    [[ -n "$WPORG_PASSWORD" ]] || fail "WPORG_PASSWORD is required unless DRY_RUN=1"
fi

SVN_AUTH_ARGS=(--non-interactive --no-auth-cache)
if [[ -n "$WPORG_USERNAME" && -n "$WPORG_PASSWORD" ]]; then
    SVN_AUTH_ARGS+=(--username "$WPORG_USERNAME" --password "$WPORG_PASSWORD")
fi

WORKDIR="$(mktemp -d)"
trap 'rm -rf "$WORKDIR"' EXIT

SVN_DIR="$WORKDIR/svn"

svn checkout --depth immediates "${SVN_AUTH_ARGS[@]}" "$SVN_URL" "$SVN_DIR" >/dev/null
svn update --set-depth infinity "${SVN_AUTH_ARGS[@]}" "$SVN_DIR/assets" >/dev/null

# Keep editable source files in Git, but do not publish them to WordPress.org SVN.
rsync -a --delete --exclude '.svn/' --exclude '*.psd' "$ASSETS_DIR"/ "$SVN_DIR/assets"/

svn_status_sync "$SVN_DIR/assets"

PENDING_CHANGES="$(svn status "$SVN_DIR/assets")"

if [[ -z "$PENDING_CHANGES" ]]; then
    echo "No WordPress.org asset changes to publish."
    exit 0
fi

echo "Prepared WordPress.org asset sync"
printf '%s\n' "$PENDING_CHANGES"

if [[ "$DRY_RUN" == "1" ]]; then
    echo "Dry run only; no SVN commit created."
    exit 0
fi

svn commit "${SVN_AUTH_ARGS[@]}" -m "Sync assets from main" "$SVN_DIR/assets"
