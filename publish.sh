#!/usr/bin/env bash
set -euo pipefail

PKG=${1:-}
VERSION=${2:-}
DRY_RUN=${3:-false} # optional third argument

if [[ -z "$PKG" || -z "$VERSION" ]]; then
  echo "Usage: ./publish.sh <package> <version> [dry-run]"
  exit 1
fi

MONOREPO_ROOT=$(git rev-parse --show-toplevel)
SUBMODULE_PATH="packages/${PKG}"

cd "$MONOREPO_ROOT"

is_submodule() {
  git submodule status -- "$1" &>/dev/null
}

if ! is_submodule "$SUBMODULE_PATH"; then
  echo "❌ $SUBMODULE_PATH is not a git submodule"
  exit 1
fi

echo "---------------------------------------"
echo "Publishing package: ${PKG}"
echo "Version: ${VERSION}"
echo "DRY_RUN: ${DRY_RUN}"
echo "---------------------------------------"

run() {
  if [[ "$DRY_RUN" == true ]]; then
    echo "🟡 DRY-RUN: $*"
  else
    "$@"
  fi
}

# -------------------------
# Skip dirty checks in dry-run
# -------------------------
if [[ "$DRY_RUN" != true ]]; then
  # Ensure monorepo is clean
  if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "❌ Monorepo working tree is dirty. Commit or stash first."
    exit 1
  fi
fi

cd "$SUBMODULE_PATH"

if [[ "$DRY_RUN" != true ]]; then
  # Ensure submodule is clean
  if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "❌ Submodule ${PKG} has uncommitted changes."
    exit 1
  fi
fi

# Determine default branch
DEFAULT_BRANCH=$(git symbolic-ref --short refs/remotes/origin/HEAD 2>/dev/null || echo "main")
DEFAULT_BRANCH="${DEFAULT_BRANCH#origin/}"
echo "Default branch: ${DEFAULT_BRANCH}"

# DEFAULT_BRANCH=${MONOREPO_DEFAULT_BRANCH:-main}
run git fetch origin
if git show-ref --verify --quiet "refs/heads/$DEFAULT_BRANCH"; then
    run git checkout "$DEFAULT_BRANCH"
else
    # Create local branch tracking remote if it doesn't exist
    run git checkout -b "$DEFAULT_BRANCH" "origin/$DEFAULT_BRANCH"
fi


# Switch to default branch
run git checkout "$DEFAULT_BRANCH"

# Create tag if it doesn't exist
if git rev-parse "$VERSION" >/dev/null 2>&1; then
  echo "⚠️ Tag $VERSION already exists locally, skipping creation"
else
  run git tag -a "$VERSION" -m "Release $VERSION"
fi

# Push commits + tag
run git push origin "$DEFAULT_BRANCH"
run git push origin "$VERSION" || echo "⚠️ Tag $VERSION may already exist on remote"

cd "$MONOREPO_ROOT"

# Update submodule pointer
run git add "$SUBMODULE_PATH"
if ! git diff --cached --quiet; then
  run git commit -m "chore(${PKG}): bump to ${VERSION}"
  run git push
else
  echo "ℹ️  No submodule pointer change to commit"
fi

echo "---------------------------------------"
echo "✅ Publish complete for ${PKG} ${VERSION}"
echo "---------------------------------------"
