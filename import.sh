#!/usr/bin/env bash
set -e

GITHUB_USER="kigathi-chege"

PACKAGES=(
  "billing:lyre-billing"
  "commerce:lyre-commerce"
  "content:lyre-content"
  "facet:lyre-facet"
  "file:lyre-file"
  "guest:lyre-guest"
  "lyre:lyre"
  "school:lyre-school"
  "settings:lyre-settings"
)

# -------------------------------
# Add remotes only (no tag fetch)
# -------------------------------
for entry in "${PACKAGES[@]}"; do
  PKG="${entry%%:*}"
  OLD_REPO="${entry##*:}"

  REMOTE_NAME="${PKG}-old"
  REPO_URL="https://github.com/${GITHUB_USER}/${OLD_REPO}.git"

  if ! git remote | grep -q "^${REMOTE_NAME}$"; then
    git remote add "${REMOTE_NAME}" "${REPO_URL}"
  fi
done

# -------------------------------
# Retag using remote tag list (no fetch)
# -------------------------------
for entry in "${PACKAGES[@]}"; do
  PKG="${entry%%:*}"
  REMOTE_NAME="${PKG}-old"

  echo "Retagging $PKG..."

  git ls-remote --tags "${REMOTE_NAME}" \
    | awk '{print $2}' \
    | sed 's#refs/tags/##' \
    | grep -E '^(v)?[0-9]+\.[0-9]+\.[0-9]+$' \
    | while read -r TAG; do

      NEW_TAG="${PKG}/${TAG}"

      # skip if already exists
      if git rev-parse "${NEW_TAG}" >/dev/null 2>&1; then
        continue
      fi

      HASH=$(git ls-remote --tags "${REMOTE_NAME}" "refs/tags/${TAG}" | awk '{print $1}')

      git tag "${NEW_TAG}" "${HASH}"
      echo "Created ${NEW_TAG}"
    done
done

echo "DONE. Push tags with:"
echo "  git push origin --tags --force"
