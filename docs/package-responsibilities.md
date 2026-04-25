# Package Responsibilities And Boundaries

## Classification
- **Foundational**: `lyre`
- **Shared capability packages**: `file`, `facet`, `settings`, `guest`
- **Domain packages**: `content`, `billing`, `commerce`, `school`

## `lyre/lyre`
### Responsibility
Core conventions and runtime primitives for all other Lyre packages.

### Owns
- Base classes: `Lyre\Model`, `Lyre\Repository`, `Lyre\Controller`, `Lyre\Resource`, `Lyre\Policy`, `Lyre\Observer`
- Helper/discovery system in `src/helpers/helpers.php`
- Core config `src/config/lyre.php`
- Tenancy primitives (`Tenant`, `TenantAssociation`, tenancy middleware)
- CLI scaffolding commands (`lyre:all`, repository/stub/cache helpers)

### Do not put here
- Package-specific domain logic (billing/content/commerce/etc.)
- integration-specific APIs for one domain only

### Public contracts
- helper function names and expected behavior
- model config discovery conventions (`generateConfig`, config path arrays)
- base response envelope behavior (`__response`)

## `lyre/content`
### Responsibility
Composable content/CMS structures: pages, sections, nested sections, texts, buttons, data bindings, menus, interactions, articles.

### Owns
- CMS domain models and APIs (`Page`, `Section`, `Menu`, `Data`, etc.)
- section data resolution (`DataRepository::resolve/build`)
- content Filament resources/plugin

### Depends on
- `lyre/lyre` core conventions
- `lyre/file` and `lyre/facet` (declared package dependencies)

### Boundary notes
- Content structures and section relation shape are public-facing contracts for frontend consumers.
- Avoid renaming/removing section-related API fields without coordinated frontend update.

## `lyre/file`
### Responsibility
Media/file and attachment management.

### Owns
- `File` and `Attachment` models/repositories/controllers/resources
- stream/download endpoints
- file gallery Livewire component + Filament field/actions

### Boundary notes
- Stream/download route shape is externally consumable.
- Attachment relation behavior should stay backward compatible.

## `lyre/facet`
### Responsibility
Taxonomy and tagging through facets/facet values and faceted entity relations.

### Owns
- `Facet`, `FacetValue`, `FacetedEntity`
- hierarchy endpoints for facets and facet values
- `HasFacet` concern and facet assignment behavior

### Boundary notes
- hierarchy endpoints and relation names (`facetValues`) are integration-sensitive.

## `lyre/settings`
### Responsibility
Application/tenant settings key-value storage and retrieval.

### Owns
- `Setting` model + repository + controller/resource + Filament resource
- global `setting()` helper

### Boundary notes
- `Setting::get/set` tenant key behavior is observable contract.
- Route file exists but is currently empty; changes here should be intentional and documented.

## `lyre/guest`
### Responsibility
Guest user/session tracking and guest-to-user transition logic.

### Owns
- `Guest` model/repository
- `EnsureGuestUser` middleware
- auth event subscriber and merge job trigger flow

### Boundary notes
- Middleware writes/reads guest UUID headers/cookies and may auto-create auth users.
- Changing this behavior has cross-package effects (commerce/content interactions).

## `lyre/billing`
### Responsibility
Subscription and billing entities plus payment-related flows.

### Owns
- billables, plans, subscriptions, invoices, transactions, payment methods
- webhook endpoint and subscription action routes
- payment orchestration layer (`PaymentManager`) and provider adapters (`PaymentGatewayInterface`)
- billing-related Filament resources

### Boundary notes
- API route names and transaction/subscription workflow semantics are public.
- Verify mixed app/package namespace references before broad refactor.

## `lyre/commerce`
### Responsibility
Commerce domain for product catalog, cart, checkout, pricing/coupon/order flows.

### Owns
- product/order/coupon/location models and APIs
- cart/checkout endpoints and supporting services
- commerce config and install/reset commands

### Boundary notes
- Route prefix is configurable via `config('commerce.route_prefix', 'api')`.
- Route middleware includes `Lyre\Guest\Http\Middleware\EnsureGuestUser`; guest package compatibility is required.

## `lyre/school`
### Responsibility
Education domain for assessments/tasks/attempts/answers.

### Owns
- school models, repositories, controllers/resources
- publish/submit action routes for assessment flows

### Boundary notes
- Keep educational domain concerns here; do not move to core.

## Cross-Package Change Rules
- If editing core helper/discovery behavior (`lyre`), test all packages that rely on it.
- If editing shared relation names in one package, audit any other package querying those relations.
- If changing API route shape in any package, update consuming app route usage and docs in same change.
- If package dependencies change, update package `composer.json`, root package map docs, and release notes.
