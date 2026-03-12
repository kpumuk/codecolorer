#!/usr/bin/env bash

set -euo pipefail

usage() {
    cat <<'EOF'
Usage: bin/release-wordpress-org.sh <tag>

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

extract_version_from_plugin_file() {
    sed -n 's/^ \* Version: //p' | head -n 1
}

extract_constant_version() {
    sed -n "s/^define('CODECOLORER_VERSION', '\\([^']*\\)');/\\1/p" | head -n 1
}

extract_stable_tag() {
    sed -n 's/^Stable tag: //Ip' | head -n 1 | tr -d '\r'
}

git_show_tag_file() {
    local tag="$1"
    local path="$2"

    git -C "$ROOT" show "$tag:$path" || fail "Unable to read $path from tag $tag"
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

TAG="${1:-${TAG:-}}"
[[ -n "$TAG" ]] || {
    usage
    exit 1
}

[[ "$TAG" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] || fail "Tag must look like 0.10.2"

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SVN_URL="${SVN_URL:-https://plugins.svn.wordpress.org/codecolorer}"
DRY_RUN="${DRY_RUN:-0}"
WPORG_USERNAME="${WPORG_USERNAME:-}"
WPORG_PASSWORD="${WPORG_PASSWORD:-}"
DISTIGNORE_FILE="$ROOT/.distignore"
ASSETS_DIR="$ROOT/.wordpress-org/assets"

require_command git
require_command rsync
require_command svn
require_command tar

require_path "$DISTIGNORE_FILE" file
require_path "$ASSETS_DIR" dir

git -C "$ROOT" rev-parse -q --verify "refs/tags/$TAG^{commit}" >/dev/null \
    || fail "Tag $TAG does not exist locally"

TAG_COMMIT="$(git -C "$ROOT" rev-parse "refs/tags/$TAG^{commit}")"

git -C "$ROOT" show-ref --verify --quiet refs/remotes/origin/main \
    || fail "origin/main is not available locally"

git -C "$ROOT" merge-base --is-ancestor "$TAG_COMMIT" refs/remotes/origin/main \
    || fail "Tag $TAG is not reachable from origin/main"

PLUGIN_FILE_CONTENT="$(git_show_tag_file "$TAG" codecolorer.php)"
README_FILE_CONTENT="$(git_show_tag_file "$TAG" readme.txt)"

PLUGIN_VERSION="$(printf '%s\n' "$PLUGIN_FILE_CONTENT" | extract_version_from_plugin_file)"
CONSTANT_VERSION="$(printf '%s\n' "$PLUGIN_FILE_CONTENT" | extract_constant_version)"
STABLE_TAG="$(printf '%s\n' "$README_FILE_CONTENT" | extract_stable_tag)"

[[ "$PLUGIN_VERSION" == "$TAG" ]] || fail "Plugin header version $PLUGIN_VERSION does not match tag $TAG"
[[ "$CONSTANT_VERSION" == "$TAG" ]] || fail "CODECOLORER_VERSION $CONSTANT_VERSION does not match tag $TAG"
[[ "$STABLE_TAG" == "$TAG" ]] || fail "Stable tag $STABLE_TAG does not match tag $TAG"

svn info "$SVN_URL/tags/$TAG" >/dev/null 2>&1 && fail "SVN tag $TAG already exists"

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

EXPORT_DIR="$WORKDIR/export"
DIST_DIR="$WORKDIR/dist"
SVN_DIR="$WORKDIR/svn"

mkdir -p "$EXPORT_DIR" "$DIST_DIR"

git -C "$ROOT" archive "$TAG" | tar -x -C "$EXPORT_DIR"
rsync -a --delete --exclude-from="$DISTIGNORE_FILE" "$EXPORT_DIR"/ "$DIST_DIR"/

for forbidden_path in .wordpress-org tests .github; do
    [[ ! -e "$DIST_DIR/$forbidden_path" ]] || fail "Dist tree still contains $forbidden_path"
done

svn checkout --depth immediates "${SVN_AUTH_ARGS[@]}" "$SVN_URL" "$SVN_DIR" >/dev/null
svn update --set-depth infinity "${SVN_AUTH_ARGS[@]}" "$SVN_DIR/trunk" "$SVN_DIR/assets" >/dev/null

rsync -a --delete --exclude '.svn/' "$DIST_DIR"/ "$SVN_DIR/trunk"/
rsync -a --delete --exclude '.svn/' "$ASSETS_DIR"/ "$SVN_DIR/assets"/

svn_status_sync "$SVN_DIR"

svn copy "$SVN_DIR/trunk" "$SVN_DIR/tags/$TAG" >/dev/null

echo "Prepared WordPress.org release $TAG"
svn status "$SVN_DIR"

if [[ "$DRY_RUN" == "1" ]]; then
    echo "Dry run only; no SVN commit created."
    exit 0
fi

svn commit "${SVN_AUTH_ARGS[@]}" -m "Release $TAG" "$SVN_DIR"
