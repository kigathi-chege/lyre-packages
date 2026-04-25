# Package Matrix

| Package | Type | Key Provider | API Routes | Filament Plugin | Notes |
|---|---|---|---|---|---|
| `lyre/lyre` | Foundation | `Lyre\\Providers\\LyreServiceProvider` | none loaded by default in provider | `Lyre\\Filament\\Plugins\\LyreFilamentPlugin` | Core base classes/helpers/config/tenancy |
| `lyre/content` | Domain CMS | `Lyre\\Content\\Providers\\LyreContentServiceProvider` | `api/articles`, `api/pages`, `api/sections`, `api/menu`, `api/interactiontypes`, `api/interactions` | `Lyre\\Content\\Filament\\Plugins\\LyreContentFilamentPlugin` | Depends on `file` + `facet`; registers both plugins |
| `lyre/file` | Shared capability | `Lyre\\File\\Providers\\LyreFileServiceProvider` | `api/files`, `api/files/stream/{slug}.{extension}`, `api/files/download/{slug}.{extension}` | `Lyre\\File\\Filament\\Plugins\\LyreFileFilamentPlugin` | Media + attachment domain |
| `lyre/facet` | Shared capability | `Lyre\\Facet\\Providers\\LyreFacetServiceProvider` | `api/facets`, `api/facetvalues`, hierarchy endpoints | `Lyre\\Facet\\Filament\\Plugins\\LyreFacetFilamentPlugin` | Taxonomy/hierarchy tagging |
| `lyre/settings` | Shared capability | `Lyre\\Settings\\Providers\\LyreSettingsServiceProvider` | route file exists but currently empty | `Lyre\\Settings\\Filament\\Plugins\\LyreSettingsFilamentPlugin` | Settings model + `setting()` helper |
| `lyre/guest` | Shared capability | `Lyre\\Guest\\Providers\\LyreGuestServiceProvider` | none loaded by default in provider | none | Guest identity middleware/listener flow |
| `lyre/billing` | Domain billing | `Lyre\\Billing\\Providers\\LyreBillingServiceProvider` | subscriptions/plans/paymentmethods + webhook + approve/subscribe routes | `Lyre\\Billing\\Filament\\Plugins\\LyreBillingFilamentPlugin` | Billing/subscription/payment entities |
| `lyre/commerce` | Domain commerce | `Lyre\\Commerce\\Providers\\LyreCommerceServiceProvider` | API resources + `cart/*` + `checkout/*` | `Lyre\\Commerce\\Filament\\Plugins\\LyreCommerceFilamentPlugin` | Uses guest middleware; configurable route prefix |
| `lyre/school` | Domain education | `Lyre\\School\\Providers\\LyreSchoolServiceProvider` | assessment/task resources + publish/submit routes | `Lyre\\School\\Filament\\Plugins\\LyreSchoolFilamentPlugin` | Assessment and task workflows |

## Release Tooling
- Single package: `publish.sh`
- Multi package: `release` (supports `--dry-run`, `all`, `changed`, `auto`, `since <ref>`)
