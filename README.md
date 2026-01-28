# Lyre Monorepo – Cloning & Usage Guide

This repository is the **Lyre monorepo**, which acts as the canonical development hub for the Lyre ecosystem.

It contains:

- A top-level monorepo
- Individual Lyre packages mounted as **git submodules** under `packages/`
- Tooling scripts (`publish.sh`, `import.sh`) to manage publishing and synchronization

This document explains **how to clone**, **initialize**, and **use** the repo correctly.

---

## Repository Structure

```
lyre/
├── packages/
│   ├── lyre/          # Core framework (submodule)
│   ├── billing/       # lyre-billing (submodule)
│   ├── commerce/      # lyre-commerce (submodule)
│   ├── content/       # lyre-content (submodule)
│   ├── facet/         # lyre-facet (submodule)
│   ├── file/          # lyre-file (submodule)
│   ├── guest/         # lyre-guest (submodule)
│   ├── school/        # lyre-school (submodule)
│   └── settings/      # lyre-settings (submodule)
├── release.sh         # Release monorepo
├── publish.sh         # Publish package → individual repo
└── .gitmodules
```

Each directory inside `packages/` is **its own Git repository**, linked as a submodule.

---

## Cloning the Monorepo (Correct Way)

### Option 1: One-step clone (recommended)

```bash
git clone --recurse-submodules https://github.com/kigathi-chege/lyre-packages.git
```

This will:

- Clone the monorepo
- Automatically clone **all package submodules**

---

### Option 2: Clone + init manually

```bash
git clone https://github.com/kigathi-chege/lyre-packages.git
cd lyre-packages
git submodule update --init --recursive
```

Use this if you forgot `--recurse-submodules`.

---

## Working With the Repo

### Updating all packages to their latest commits

```bash
git submodule update --remote --merge
```

This pulls the latest commits from each package’s default branch.

---

### Working on a single package

```bash
cd packages/billing
git checkout develop   # or any branch you need
```

You are now **inside the package repo**, not the monorepo.

Commits made here belong to that package only.

---

### Returning to the monorepo

```bash
cd ../..
```

After updating submodules, commit the pointer updates:

```bash
git add packages/*
git commit -m "Update package submodules"
```

---

## Installing Lyre into a Laravel Project

### Install all Lyre packages at once

```bash
composer require lyre/monorepo
```

This will:

- Pull all Lyre packages from Packagist in production
- Resolve versions normally via Composer

---

### Local development (monorepo symlinks)

When working locally inside this monorepo, the following Composer config enables symlinking:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "packages/*",
      "options": { "symlink": true }
    }
  ]
}
```

Result:

- No re-installing packages
- Instant changes across all packages
- Single-source-of-truth development

---

# Multi-Package Release CLI

`release` is a Bash script for safely releasing multiple packages in a monorepo that uses Git submodules. It supports version bumps, automated detection of changes, and updating submodule pointers.

---

## Features

- Release individual or multiple packages
- Supports `auto` mode to release only changed packages
- Safely updates `composer.json` versions
- Works with Git submodules
- Supports `dry-run` mode for safe previews
- Updates monorepo pointers automatically
- Works from any directory inside the repository

---

## Requirements

- Bash (`#!/usr/bin/env bash`)
- `git`
- `jq` (for updating `composer.json`)

Ensure that `submodule-publish.sh` is executable:

```bash
chmod +x submodule-publish.sh
```

````

---

## Usage

```bash
release [options] <spec> [<spec>...]
```

### Options

| Option          | Description                        |
| --------------- | ---------------------------------- |
| `-n, --dry-run` | Show what would happen, do nothing |
| `-h, --help`    | Show this help message             |

### Specs

Specs define which packages and versions to release. They can be in any of these forms:

- `content` — release the package with auto version detection
- `content 1.4.0` — release `content` with a specific version
- `content 1.4.0 "feat(content): message"` — release with custom commit message
- `content:version=1.4.0,message="feat(content): message"` — key-value style
- `file:version=1.3.2`
- `lyre:version=2.0.0,message="breaking change"`

---

## Examples

Release a single package:

```bash
release content
```

Release multiple packages:

```bash
release content file
```

Release with specific version:

```bash
release content 1.4.0
```

Release with version and custom commit message:

```bash
release content 1.4.0 "feat(content): add new feature"
```

Key-value style release:

```bash
release content:version=1.4.0,message="feat(content): add new feature"
```

Dry-run example:

```bash
release -n content 1.4.0 "feat(content): test dry run"
```

Release all changed packages automatically:

```bash
release auto
```

Release all packages:

```bash
release all
```

Release packages changed since a Git ref:

```bash
release since main
```

---

## How It Works

1. Validates repository and submodules
2. Determines which packages to release
3. Checks out the default branch of each submodule
4. Ensures working tree is clean
5. Updates `composer.json` version safely (if applicable)
6. Commits and pushes changes for each package
7. Calls `submodule-publish.sh` for each released package
8. Updates monorepo submodule pointers in root repository
9. Supports `DRY_RUN` mode to preview actions without making changes

---

## Notes

- The script can be run from **any directory** inside the repo.
- Monorepo pointer updates ensure all released versions are reflected in the main repository.
- Safe `jq` handling prevents leftover temporary files in case of failure.
- Dry-run mode logs all commands instead of executing them.

---

## License

MIT License © nipate

````

## Publishing a Package

From the monorepo root:

```bash
./publish.sh billing 1.4.0
```

This will:

1. Split `packages/billing` into a clean history
2. Force-push it to the correct default branch of `lyre-billing`
3. Create and push the version tag
4. Preserve a backup branch automatically

⚠️ **This rewrites history of the individual repo’s default branch.**
That is intentional and safe because each repo only contains its package.

---

## Important Rules

- **Do not commit directly to `packages/*` from the monorepo root**
- Always `cd` into the package directory
- Treat each package as an independent repo
- The monorepo only tracks _which commit_ each package is on

---

## Cloning Checklist

✅ Clone monorepo
✅ Initialize submodules
✅ Work inside `packages/*`
✅ Commit submodule pointer updates in root repo

---

## Summary

- Monorepo = orchestration layer
- Packages = independent Git repos
- Submodules = clean separation + discoverability
- `lyre/monorepo` = one-command install for consumers

This setup gives you:

- Clean Git history
- Clean releases
- Easy access on GitHub
- First-class local DX

---

Happy hacking 🚀
