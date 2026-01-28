#!/usr/bin/env bash
set -euo pipefail

GITHUB_USER="kigathi-chege"

PACKAGES=(
  "billing:lyre-billing"
  "commerce:lyre-commerce"
  "content:lyre-content"
  "facet:lyre-facet"
  "file:lyre-file"
  "guest:lyre-guest"
  "school:lyre-school"
  "settings:lyre-settings"
  "lyre:lyre"
)

echo "⚠️  This will REMOVE subtree folders and re-add them as submodules."
echo "Press Ctrl+C to abort."
sleep 3

# Ensure clean working tree
if ! git diff --quiet || ! git diff --cached --quiet; then
  echo "❌ Working tree is dirty. Commit or stash first."
  exit 1
fi

for entry in "${PACKAGES[@]}"; do
  PKG="${entry%%:*}"
  REPO="${entry##*:}"

  echo "-------------------------------------"
  echo "Converting $PKG → submodule"
  echo "-------------------------------------"

  # Remove subtree directory
  git rm -rf "packages/$PKG"

  # Add submodule
  git submodule add "https://github.com/${GITHUB_USER}/${REPO}.git" "packages/$PKG"
done

git commit -m "Convert packages from subtrees to submodules"

echo "✅ Subtrees converted to submodules"
echo ""
echo "Next steps:"
echo "  git submodule update --init --recursive"
