#!/usr/bin/env bash
set -euo pipefail

PKG=$1
VERSION=$2

if [[ -z "$PKG" || -z "$VERSION" ]]; then
  echo "Usage: ./publish.sh <package> <version>"
  exit 1
fi

# -------------------------
# Determine individual repo name
# -------------------------
if [[ "$PKG" == "lyre" ]]; then
  REMOTE_REPO="lyre"
else
  REMOTE_REPO="lyre-$PKG"
fi

REMOTE_URL="https://github.com/kigathi-chege/${REMOTE_REPO}.git"

# -------------------------
# Determine default branch of individual repo
# -------------------------
DEFAULT_BRANCH=$(git ls-remote --symref "$REMOTE_URL" HEAD 2>/dev/null | awk '/^ref:/ {print $2}' | sed 's@^refs/heads/@@')

if [[ -z "$DEFAULT_BRANCH" ]]; then
  echo "Could not determine default branch of ${REMOTE_REPO}."
  exit 1
fi

echo "Default branch of ${REMOTE_REPO} is: ${DEFAULT_BRANCH}"

# -------------------------
# Backup default branch (always)
# -------------------------
git checkout "$DEFAULT_BRANCH"

TIMESTAMP=$(date +%Y%m%d%H%M%S)
BACKUP_BRANCH="${DEFAULT_BRANCH}-${TIMESTAMP}"

git branch -f "${BACKUP_BRANCH}"
echo "Created backup branch: ${BACKUP_BRANCH}"

# PUSH BACKUP to remote
git push "$REMOTE_URL" "${BACKUP_BRANCH}" --force
echo "Backup pushed to remote: ${BACKUP_BRANCH}"

# -------------------------
# Subtree split + push
# -------------------------
SUBTREE_BRANCH="release/$PKG-$VERSION"

git subtree split --prefix=packages/$PKG -b "$SUBTREE_BRANCH"
git checkout "$SUBTREE_BRANCH"

# Create tag in this branch (ensures tag exists in subtree)
git tag -f "$VERSION"

# Push changes to individual repo (force is needed)
git push "$REMOTE_URL" "$SUBTREE_BRANCH:${DEFAULT_BRANCH}" --force

# Push tag to remote
git push "$REMOTE_URL" "refs/tags/$VERSION" --force

# Cleanup
git checkout "$DEFAULT_BRANCH"
git branch -D "$SUBTREE_BRANCH"
