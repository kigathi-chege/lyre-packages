## Lyre Commerce

Lyre-native e-commerce package: models, repositories, resources, controllers, and domain services (cart, pricing, coupons, checkout). Fully aligned with Lyre patterns and query-string filtering.

### Install

1. Register provider (if package discovery is not enabled):
   - Add `Lyre\Commerce\Providers\LyreCommerceServiceProvider::class` to your app providers.
2. Publish config and migrations:
   - `php artisan vendor:publish --tag=lyre-commerce-config`
   - `php artisan vendor:publish --tag=lyre-commerce-migrations`
3. Migrate: `php artisan migrate`

### Routes

- API prefix: `config('commerce.route_prefix', 'api')`
- Resources: locations, shippingaddresses, products, productvariants, userproductvariants, productvariantprices, orders, orderitems, coupons, couponusages
- Domain: `cart/*`, `checkout/*`

### Integrations

- Uses Lyre repository helpers, resources, and controller patterns
- Supports Lyre query strings: with, relation, relation_in, range, filter, order, per_page, page, startswith, withcount, wherenull, doesnthave, random, first, unpaginated, limit, offset
- Emits events hooks (future extension) per config

```bash
php artisan db:seed --class="Lyre\\Commerce\\Database\\Seeders\\CommerceDemoSeeder"
php artisan db:seed --class="Lyre\\Commerce\\Database\\Seeders\\CommerceComprehensiveSeeder"
```
