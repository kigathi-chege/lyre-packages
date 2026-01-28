#!/usr/bin/env bash
set -euo pipefail

PKG=${1:-}
VERSION=${2:-}
DRY_RUN=${3:-false} # optional third argument

if [[ -z "$PKG" || -z "$VERSION" ]]; then
  echo "Usage: ./submodule-publish.sh <package> <version> [dry-run]"
  exit 1
fi

SUBMODULE_PATH="packages/${PKG}"
MONOREPO_ROOT=$(pwd)

[[ -d "$SUBMODULE_PATH/.git" ]] || { echo "❌ ${SUBMODULE_PATH} is not a git submodule"; exit 1; }

echo "---------------------------------------"
echo "Publishing package: ${PKG}"
echo "Version: ${VERSION}"
echo "DRY_RUN: ${DRY_RUN}"
echo "---------------------------------------"

# Ensure monorepo is clean
if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "❌ Monorepo working tree is dirty. Commit or stash first."
  exit 1
fi

cd "$SUBMODULE_PATH"

# Ensure submodule is clean
if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "❌ Submodule ${PKG} has uncommitted changes."
  exit 1
fi

# Determine default branch
DEFAULT_BRANCH=$(git symbolic-ref --short refs/remotes/origin/HEAD 2>/dev/null || echo "main")
echo "Default branch: ${DEFAULT_BRANCH}"

git checkout "$DEFAULT_BRANCH"

# Create tag if it doesn't exist
if git rev-parse "$VERSION" >/dev/null 2>&1; then
  echo "⚠️ Tag $VERSION already exists locally, skipping creation"
else
  if [[ "$DRY_RUN" == true ]]; then
    echo "🟡 DRY-RUN: git tag -a $VERSION -m 'Release $VERSION'"
  else
    git tag -a "$VERSION" -m "Release $VERSION"
  fi
fi

# Push commits + tag
if [[ "$DRY_RUN" == true ]]; then
  echo "🟡 DRY-RUN: git push origin $DEFAULT_BRANCH"
  echo "🟡 DRY-RUN: git push origin $VERSION"
else
  git push origin "$DEFAULT_BRANCH"
  git push origin "$VERSION" || echo "⚠️ Tag $VERSION may already exist on remote"
fi

cd "$MONOREPO_ROOT"

# Update submodule pointer
git add "$SUBMODULE_PATH"
if ! git diff --cached --quiet; then
  if [[ "$DRY_RUN" == true ]]; then
    echo "🟡 DRY-RUN: git commit -m 'chore($PKG): bump to $VERSION'"
    echo "🟡 DRY-RUN: git push"
  else
    git commit -m "chore(${PKG}): bump to ${VERSION}"
    git push
  fi
else
  echo "ℹ️  No submodule pointer change to commit"
fi

echo "---------------------------------------"
echo "✅ Publish complete for ${PKG} ${VERSION}"
echo "---------------------------------------"
