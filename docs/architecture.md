# Lyre Monorepo Architecture

## 1. Architecture Summary
Lyre packages follow a layered Laravel pattern centered on `lyre/lyre`:

- **Model layer**: extends `Lyre\Model` (table naming + shared trait behaviors)
- **Repository layer**: extends `Lyre\Repository` (query composition, filters, pagination)
- **Controller layer**: extends `Lyre\Controller` (RESTful actions + response envelope)
- **Resource layer**: extends `Lyre\Resource` (serialization + relation loading)
- **Policy/Observer**: package policies + observer auto-registration via helper functions

Package service providers wire repositories, observers, migrations, routes, optional commands, and sometimes Livewire components.

## 2. Package Composition Graph

- `lyre/lyre` is foundational.
- `lyre/file`, `lyre/facet`, `lyre/settings`, `lyre/guest`, `lyre/billing`, `lyre/school`, `lyre/commerce` depend on `lyre/lyre`.
- `lyre/content` depends on `lyre/lyre`, `lyre/file`, and `lyre/facet`.
- `lyre/commerce` route middleware expects `lyre/guest` classes to exist.

## 3. Bootstrap And Discovery Flow

### Service provider startup
Typical package provider behavior:
1. `register_repositories($app, <repo namespace>, <contract namespace>)`
2. `register_global_observers(<model namespace>)`
3. publish migrations/config
4. load package API routes
5. optionally register commands/Livewire components

### Naming/discovery conventions
`Lyre\Traits\BaseModelTrait::generateConfig()` resolves associated classes through `config('lyre.path.*')` arrays:
- model
- repository
- contracts
- resource
- request

Core helpers (`lyre/src/helpers/helpers.php`) support this resolution and helper registration.

## 4. HTTP Contract Pattern
Most packages expose API routes under `prefix('api')` with:
- `api` middleware
- `EnsureFrontendRequestsAreStateful`

Controllers use the base controller response helper:
- `__response(status, message, result, code, ...)`

This creates a stable envelope pattern used across packages.

## 5. Filament Integration Pattern
Each package provides a Filament plugin implementing `Filament\Contracts\Plugin`.

Common pattern:
- discover resources with `get_filament_resources_for_namespace(...)`
- register resources on panel
- optionally register dependent plugins

`LyreContentFilamentPlugin` also registers `LyreFileFilamentPlugin` and `LyreFacetFilamentPlugin`.

## 6. Multi-Tenancy Hooks
Core tenancy helper:
- `tenant()` returns bound current tenant if available.

Core observer (`Lyre\Observer`) and trait behavior use tenant association when enabled/configured.

`Lyre\Providers\LyreServiceProvider` binds tenancy model classes from `config('lyre.tenancy.*')` and adjusts middleware priority to run `SetCurrentTenant` before route binding substitution.

## 7. Release Architecture
Release is managed at monorepo root:
- `publish.sh`: publish/tag/push one package and commit submodule pointer update
- `release`: multi-package release orchestration with dry-run and auto/changed/since modes, version bumping via `jq`

Version coordination happens through package `composer.json` versions and submodule pointer commits in root.

## 8. Evidence-Backed Ambiguities
These should be verified before large refactors:
- `packages/settings/src/routes/api.php` is currently empty though provider loads it.
- `packages/billing` has mixed namespace usage in some controllers/imports (package vs app namespace assumptions).
- No root CI workflow or shared tests were detected in this monorepo root.
