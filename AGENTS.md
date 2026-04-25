# Lyre Monorepo Agent Guide

## Purpose Of This Monorepo
This repository is the canonical development workspace for the Lyre package ecosystem. It is a **meta-package + submodule monorepo** that coordinates development and release of:

- `lyre/lyre` (core runtime conventions)
- domain packages built on top of core (`content`, `file`, `facet`, `billing`, `commerce`, `guest`, `school`, `settings`)

The root `composer.json` is a metapackage (`lyre/monorepo`) that requires all Lyre packages and uses `packages/*` path repositories for local symlink development.

## How The Monorepo Is Organized
- Root contains release tooling: `release`, `publish.sh`.
- Each folder in `packages/*` is a package with its own `composer.json` and service provider.
- Packages are expected to be installable independently in Laravel apps via Composer package discovery.
- Package runtime wiring is primarily through:
  - service providers (`src/Providers/*ServiceProvider.php`)
  - helper discovery functions in `lyre/src/helpers/helpers.php`
  - Filament plugins (`src/Filament/Plugins/*FilamentPlugin.php`)
  - package API route files (`src/routes/api.php`)

## Package Map And Responsibilities
See [docs/package-responsibilities.md](/Users/chegekigathi/Projects/packages/lyre-packages/docs/package-responsibilities.md) for the full map.

High-level ownership:
- `lyre`: base Model/Repository/Controller/Resource/Policy/Observer primitives and helper discovery system.
- `content`: page/section CMS data model + API + Filament resources.
- `file`: media/files + attachment model + stream/download routes + gallery UI pieces.
- `facet`: taxonomy/facet tagging and hierarchy endpoints.
- `settings`: key/value settings model + helper.
- `guest`: guest identity/session tracking and guest-to-user merge flow.
- `billing`: subscriptions/plans/invoices/transactions + webhook + payment service integrations.
- `commerce`: catalog/cart/checkout/order domain and commerce configuration.
- `school`: educational assessment/task domain.

## How Packages Work Together
- All packages depend on `lyre/lyre` and inherit conventions from `Lyre\Model`, `Lyre\Repository`, `Lyre\Controller`, `Lyre\Resource`.
- Repositories are bound dynamically via `register_repositories(...)` using naming conventions and contract namespaces.
- Observers are auto-registered via `register_global_observers(...)`; if no package/app observer is found, `Lyre\Observer` is attached.
- Table naming for package models is affected by `config('lyre.table_prefix')` when model namespace starts with `Lyre\`.
- `content` composes other packages: it depends on `file` and `facet` and its Filament plugin installs both.
- `commerce` depends on `guest` middleware (`EnsureGuestUser`) in API routes.

## Rules For Use

### Installation and bootstrap
- Prefer normal Composer installation with package discovery.
- Publish package migrations/config only from each package provider tags/provider mapping.
- Register Filament plugins via each package plugin class (prefer `::make()`).

### Public entry points
Treat these as stable usage surfaces unless a coordinated major change is planned:
- package service providers in `composer.json -> extra.laravel.providers`
- package route endpoints in each `src/routes/api.php`
- model/repository/controller/resource contracts exposed under package namespaces
- root helper API expected by consuming apps (`__response`, `register_repositories`, `register_global_observers`, `tenant`, `setting`, etc.)

### Conventions consumers must follow
- Models intended for Lyre conventions should extend `Lyre\Model` or include equivalent required behavior.
- Repository interfaces and implementations must follow naming conventions so helper auto-binding resolves correctly.
- Avoid hardcoding table names for Lyre package models; prefix behavior is config-driven.
- If using query-string repository filters, use supported keys documented in core docs and package docs.

### Anti-patterns
- Do not duplicate API resources/routes in consuming apps when package already owns that endpoint.
- Do not bypass repository contracts with ad-hoc controller query logic unless explicitly required.
- Do not add integration-specific behavior (e.g., domain billing/provider code) into `lyre/lyre` core.

## Rules For Extend

### Where new functionality belongs
- Put cross-domain framework primitives in `packages/lyre` only.
- Put domain behavior in the owning domain package (`content`, `billing`, `commerce`, etc.).
- If functionality is optional/integration-specific, prefer a dedicated package or package-local module over core changes.

### Pattern preservation requirements
- Preserve Model -> Repository -> Controller -> Resource layering used by package controllers.
- Preserve naming/discovery conventions (`*Repository`, `*RepositoryInterface`, request/resource namespace paths).
- Preserve helper compatibility in `lyre/src/helpers/helpers.php`; many packages and apps rely on those names.
- Preserve `register_global_observers` fallback behavior unless explicitly redesigning observer strategy.

### Backward compatibility
- Consider these breaking unless versioned and documented:
  - renaming/removing routes
  - changing response envelope shape returned by `__response`
  - changing repository helper names generated from interface names
  - changing model ID/name/status constants behavior used by base controller/resource
  - changing table prefix semantics for `Lyre\` models

### Cross-package change policy
If a change in one package alters assumptions in another package:
1. Update all affected packages in the same branch.
2. Update docs in this repo and relevant package docs.
3. Run integration checks against at least one consuming Laravel app before release.
4. Use coordinated version bumps (and changelog notes where present).

## Public Contracts Vs Internal Implementation

### Public contracts (treat as stable)
- Service provider registration and published assets behavior.
- HTTP API route surfaces declared in package route files.
- Filament plugin IDs and resource registration usage.
- Model config discovery pattern via `generateConfig()` and `config('lyre.path.*')`.
- Settings helper `setting()` and tenant helper `tenant()`.

### Internal details (may change with care)
- Internal repository method internals that do not change external behavior.
- Observer implementation details that preserve observable side effects.
- Seeder/demo data specifics unless explicitly documented as required fixtures.

When uncertain, treat behavior as public if it is consumed by another package, documented, or relied upon in route/controller/resource flow.

## Testing Expectations
This monorepo currently has no unified root test suite or CI workflow checked into root.

For every behavior change:
- Run package-level tests if present.
- Run static checks/linting used by the touched package(s), if present.
- Validate API route behavior and Filament registration in a real Laravel host app.
- For cross-package changes, run at least one integration smoke flow covering affected package composition.

If tests are missing for changed behavior, add tests in the appropriate package or document explicit manual verification in PR notes.

## Release And Versioning Notes
- Package release flow is script-driven through root `release` and `publish.sh`.
- Package versions are stored in each package `composer.json` and bumped by release tooling.
- Root metapackage version is also updated by release tooling.
- Do not manually tag ad-hoc versions without aligning composer version bumps and submodule pointer updates.

## Documentation Synchronization Policy (Non-Negotiable)
Every code change must evaluate whether it changes any of:
- package responsibilities
- usage flow
- extension flow
- public API/route/contract
- configuration keys/defaults
- examples/snippets
- architecture boundaries
- release workflow

If yes, you must update in the **same change**:
- root `AGENTS.md`
- affected package `AGENTS.md`
- relevant docs in `docs/`
- relevant package `README.md` / package docs

## Instructions For Future AI Agents Before Making Changes
1. Read this file.
2. Read the nearest package-level `AGENTS.md` for every package you will touch.
3. Identify whether your change affects public contracts.
4. Implement in the owning package; avoid cross-package leakage.
5. Run validation for touched behavior.
6. Update docs per synchronization policy before finishing.
7. If evidence is ambiguous, document ambiguity explicitly instead of guessing.
