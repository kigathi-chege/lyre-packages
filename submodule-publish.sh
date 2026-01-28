#!/usr/bin/env bash
set -euo pipefail

PKG=$1
VERSION=$2

if [[ -z "$PKG" || -z "$VERSION" ]]; then
  echo "Usage: ./publish.sh <package> <version>"
  exit 1
fi

SUBMODULE_PATH="packages/${PKG}"

if [[ ! -d "$SUBMODULE_PATH/.git" ]]; then
  echo "❌ ${SUBMODULE_PATH} is not a git submodule"
  exit 1
fi

echo "---------------------------------------"
echo "Publishing package: ${PKG}"
echo "Version: ${VERSION}"
echo "---------------------------------------"

# Ensure monorepo is clean
if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "❌ Monorepo working tree is dirty. Commit or stash first."
  exit 1
fi

# Enter submodule
cd "$SUBMODULE_PATH"

# Ensure submodule is clean
if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "❌ Submodule ${PKG} has uncommitted changes."
  exit 1
fi

# Determine default branch
DEFAULT_BRANCH=$(git symbolic-ref --short refs/remotes/origin/HEAD | sed 's@^origin/@@')

echo "Default branch: ${DEFAULT_BRANCH}"

# Ensure we're on default branch
git checkout "$DEFAULT_BRANCH"

# Create tag
git tag -a "$VERSION" -m "Release ${VERSION}"

# Push commits + tag
git push origin "$DEFAULT_BRANCH"
git push origin "$VERSION"

echo "✅ Submodule ${PKG} pushed and tagged"

# Return to monorepo root
cd ../..

# Update submodule pointer
git add "$SUBMODULE_PATH"
git commit -m "chore(${PKG}): bump to ${VERSION}"

git push

echo "---------------------------------------"
echo "✅ Publish complete for ${PKG} ${VERSION}"
echo "---------------------------------------"
