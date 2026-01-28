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
├── publish.sh         # Publish package → individual repo
├── import.sh          # Import package → monorepo
└── .gitmodules
```

Each directory inside `packages/` is **its own Git repository**, linked as a submodule.

---

## Cloning the Monorepo (Correct Way)

### Option 1: One-step clone (recommended)

```bash
git clone --recurse-submodules https://github.com/kigathi-chege/lyre.git
```

This will:

- Clone the monorepo
- Automatically clone **all package submodules**

---

### Option 2: Clone + init manually

```bash
git clone https://github.com/kigathi-chege/lyre.git
cd lyre
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
