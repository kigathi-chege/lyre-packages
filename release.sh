#!/usr/bin/env bash
set -euo pipefail

#############################################
# Globals
#############################################
DRY_RUN=false
SHOW_HELP=false
ARGS=()
MONOREPO_DEFAULT_BRANCH=main

#############################################
# Parse flags
#############################################
while [[ $# -gt 0 ]]; do
  case "$1" in
    -n|--dry-run)
      DRY_RUN=true
      shift
      ;;
    -h|--help)
      SHOW_HELP=true
      shift
      ;;
    *)
      ARGS+=("$1")
      shift
      ;;
  esac
done

#############################################
# Help
#############################################
if [[ "$SHOW_HELP" == true ]]; then
  cat <<'EOF'
release — multi-package release CLI

USAGE:
  release [options] <spec> [<spec>...]

OPTIONS:
  -n, --dry-run    Show what would happen, do nothing
  -h, --help       Show this help message

SPECS (any mix):
  content
  content 1.4.0
  content 1.4.0 "feat(content): message"
  content:version=1.4.0,message="feat..."
  file:version=1.3.2
  lyre:version=2.0.0,message="breaking change"

EXAMPLES:
  release content file
  release content 1.4.0
  release -n content 1.4.0 "feat..."
  release content:version=1.4.0,message="feat..."

NOTES:
- Runs safely from any directory inside the repo
- Uses submodules
- Publishes only what changed

EOF
  exit 0
fi

command -v jq >/dev/null || {
  echo "❌ jq is required to update composer.json"
  exit 1
}

#############################################
# Repo root
#############################################
REPO_ROOT=$(git rev-parse --show-toplevel 2>/dev/null || true)
[[ -z "$REPO_ROOT" ]] && { echo "❌ Not in a git repo"; exit 1; }
cd "$REPO_ROOT"

[[ -x ./submodule-publish.sh ]] || {
  echo "❌ submodule-publish.sh not found or not executable"
  exit 1
}

#############################################
# Helper
#############################################
run() {
  if $DRY_RUN; then
    echo "🟡 DRY-RUN: $*"
  else
    "$@"
  fi
}

#############################################
# Discover packages
#############################################
discover_all_packages() {
  git submodule status --recursive | awk '{print $2}' | awk -F/ '{print $2}'
}

discover_changed_packages() {
  git submodule status --recursive | awk '$1 ~ /^[+-]/ {print $2}' | awk -F/ '{print $2}'
}

discover_since_packages() {
  local ref="$1"
  git diff --name-only "$ref"...HEAD | grep '^packages/' | awk -F/ '{print $2}' | sort -u
}

if [[ "${ARGS[0]:-}" == "all" ]]; then
  mapfile -t ARGS < <(discover_all_packages)
elif [[ "${ARGS[0]:-}" == "changed" ]]; then
  mapfile -t ARGS < <(discover_changed_packages)
elif [[ "${ARGS[0]:-}" == "since" ]]; then
  [[ -z "${ARGS[1]:-}" ]] && { echo "❌ since requires a git ref"; exit 1; }
  mapfile -t ARGS < <(discover_since_packages "${ARGS[1]}")
fi

AUTO_REQUESTED=false
[[ "${ARGS[0]:-}" == "auto" ]] && AUTO_REQUESTED=true

if $AUTO_REQUESTED && [[ ${#ARGS[@]} -gt 1 ]]; then
  echo "❌ 'auto' cannot be combined with other specs"
  exit 1
fi

if $AUTO_REQUESTED; then
  AUTO_MODE=true
  ARGS=($(discover_changed_packages))
else
  AUTO_MODE=false
fi

#############################################
# Version utilities
#############################################
latest_tag() {
  git describe --tags --abbrev=0 2>/dev/null || echo "0.0.0"
}

bump_version() {
  local v="$1" type="$2"
  IFS=. read -r major minor patch <<< "${v#v}"

  case "$type" in
    major) ((major++)); minor=0; patch=0 ;;
    minor) ((minor++)); patch=0 ;;
    patch) ((patch++)) ;;
  esac

  echo "$major.$minor.$patch"
}

detect_bump() {
  git log --oneline "$(latest_tag)"..HEAD | grep -qi breaking && echo major && return
  git log --oneline "$(latest_tag)"..HEAD | grep -qi feat && echo minor && return
  echo patch
}

is_version() {
  [[ "$1" =~ ^v?[0-9]+\.[0-9]+\.[0-9]+ ]]
}

#############################################
# Parse specs
#############################################
parse_specs() {
  local specs=("$@")
  local i=0

  while [[ $i -lt ${#specs[@]} ]]; do
    local token="${specs[$i]}"

    if [[ "$token" == *:* ]]; then
      PKG="${token%%:*}"
      VERSION=""
      MESSAGE=""

      IFS=',' read -ra KV <<< "${token#*:}"
      for pair in "${KV[@]}"; do
        case "$pair" in
          version=*) VERSION="${pair#version=}" ;;
          v=*)       VERSION="${pair#v=}" ;;
          message=*) MESSAGE="${pair#message=}" ;;
          m=*)       MESSAGE="${pair#m=}" ;;
        esac
      done

      echo "$PKG|$VERSION|$MESSAGE"
      ((i++))
    else
      PKG="$token"
      VERSION="${specs[$((i+1))]:-}"
      MESSAGE="${specs[$((i+2))]:-}"

      [[ -n "$VERSION" && ! is_version "$VERSION" ]] && VERSION=""
      [[ "$MESSAGE" == *:* || "$MESSAGE" == -* ]] && MESSAGE=""

      echo "$PKG|$VERSION|$MESSAGE"
      ((i+=1))
      [[ -n "$VERSION" ]] && ((i+=1))
      [[ -n "$MESSAGE" ]] && ((i+=1))
    fi
  done
}

#############################################
# Execute releases
#############################################
mapfile -t TASKS < <(parse_specs "${ARGS[@]}")

[[ ${#TASKS[@]} -eq 0 ]] && {
  echo "ℹ️  Nothing to release"
  exit 0
}

# Validate all packages
for task in "${TASKS[@]}"; do
  PKG="${task%%|*}"
  [[ -d "packages/$PKG" ]] || { echo "❌ Unknown package: $PKG"; exit 1; }
done

UPDATED_SUBMODULES=()

for task in "${TASKS[@]}"; do
  IFS='|' read -r PKG VERSION MESSAGE <<< "$task"
  PKG_PATH="packages/$PKG"
  [[ ! -d "$PKG_PATH/.git" ]] && { echo "❌ $PKG is not a submodule"; exit 1; }
  git submodule update --init "$PKG_PATH"

  echo ""
  echo "========================================"
  echo "📦 $(date '+%Y-%m-%d %H:%M:%S') Releasing $PKG"
  echo "  Version : ${VERSION:-<unchanged>}"
  echo "  Message : ${MESSAGE:-<auto>}"
  echo "========================================"

  pushd "$PKG_PATH" >/dev/null

  DEFAULT_BRANCH=$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@')
  [[ -z "$DEFAULT_BRANCH" ]] && DEFAULT_BRANCH=main

  run git checkout "$DEFAULT_BRANCH"

  CURRENT_BRANCH=$(git branch --show-current)
  [[ "$CURRENT_BRANCH" != "$DEFAULT_BRANCH" ]] && { echo "❌ Not on default branch"; exit 1; }

  git status --porcelain | grep -q . && { echo "❌ Working tree not clean in $PKG"; exit 1; }

  if $AUTO_MODE; then
    if git log "$(latest_tag)"..HEAD --oneline | grep -q .; then
      BASE=$(latest_tag)
      TYPE=$(detect_bump)
      VERSION=$(bump_version "$BASE" "$TYPE")
    else
      VERSION=""
    fi
  fi

  if [[ -n "$VERSION" && -f composer.json ]]; then
    TMP_JSON="$(mktemp)"
    trap 'rm -f "$TMP_JSON"' EXIT
    run jq --arg v "$VERSION" '.version = $v' composer.json > "$TMP_JSON"
    run mv "$TMP_JSON" composer.json
    trap - EXIT
    run git add composer.json
  fi

  run git add .

  if ! git diff --cached --quiet; then
    COMMIT_MSG="${MESSAGE:-"chore($PKG): release $VERSION"}"
    run git commit -m "$(printf '%q' "$COMMIT_MSG")"
    run git push origin "$DEFAULT_BRANCH" || {
        echo "❌ Failed to push $PKG to $DEFAULT_BRANCH"
        exit 1
    }
  else
    echo "ℹ️  No changes to commit"
  fi

  popd >/dev/null

  if [[ -n "$VERSION" ]]; then
    # Pass DRY_RUN to submodule-publish.sh
    ./submodule-publish.sh "$PKG" "$VERSION" "$DRY_RUN"
  fi

  UPDATED_SUBMODULES+=("$PKG")
done

# Commit updated submodule pointers
if [[ ${#UPDATED_SUBMODULES[@]} -gt 0 ]]; then
  echo ""
  echo "📦 Updating submodule pointers in monorepo root..."

  for pkg in "${UPDATED_SUBMODULES[@]}"; do
    run git add "packages/$pkg"
  done

  if ! git diff --cached --quiet; then
    COMMIT_MSG="chore: update submodule pointers for release"
    run git commit -m "$COMMIT_MSG"
    ROOT_BRANCH=$(git symbolic-ref --quiet --short HEAD || echo "$MONOREPO_DEFAULT_BRANCH")
    run git push origin "$ROOT_BRANCH"
  else
    echo "ℹ️  Monorepo pointers already up-to-date"
  fi
fi

echo ""
echo "✅ Release finished${DRY_RUN:+ (dry-run)}"
