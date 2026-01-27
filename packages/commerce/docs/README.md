# Lyre Commerce - Complete Documentation

## Overview

Lyre Commerce is a fully-featured e-commerce package built on Lyre patterns. It provides product management, cart, checkout, coupon system, pricing engine, and Mpesa payment integration.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Models & Relationships](#models--relationships)
- [API Endpoints](#api-endpoints)
- [Services](#services)
- [Events & Observers](#events--observers)
- [Filament Admin](#filament-admin)
- [Testing](#testing)
- [Payment Integration](#payment-integration)
- [Fulfillment Flow](#fulfillment-flow)

## Installation

### 1. Add Package to Composer

```json
{
  "require": {
    "lyre/commerce": "*@dev"
  },
  "repositories": [
    {
      "type": "path",
      "url": "../../packages/lyre/commerce"
    }
  ]
}
```

### 2. Install Package

```bash
composer update lyre/commerce
php artisan lyre:commerce:install --seed-terms
php artisan migrate
```

### 3. Register Filament Plugin

In `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Lyre\Commerce\Filament\Plugins\LyreCommerceFilamentPlugin;

->plugins([
    // ... other plugins
    LyreCommerceFilamentPlugin::make(),
])
```

### 4. Seed Demo Data

```bash
php artisan db:seed --class="Lyre\Commerce\Database\Seeders\CommerceDemoSeeder"
```

## Configuration

Publish config: `php artisan vendor:publish --tag=lyre-commerce-config`

### config/commerce.php

```php
return [
    'route_prefix' => 'api',
    'default_currency' => 'USD',
    'default_payment_term' => 'prepaid',
    'stock' => [
        'decrease_on' => 'paid', // paid|confirmed
        'restore_on_cancel' => true,
    ],
    'events' => [
        'emit_order_events' => true,
    ],
    'tax' => [
        'enabled' => false,
        'rate_percent' => 0,
    ],
    'payment_terms' => [
        'prepaid' => 'require_payment_before_fulfillment',
        'cod' => 'fulfillment_before_payment',
        'postpaid_net30' => 'fulfillment_before_payment',
        'deposit_balance' => 'partial_payment_then_fulfillment',
        'subscription' => 'recurring',
    ],
];
```

## Models & Relationships

### Core Models

- **Location**: Delivery locations with coordinates and fees
- **ShippingAddress**: User shipping addresses
- **Product**: Base product catalog
- **ProductVariant**: Product variants (size, color, etc.)
- **UserProductVariant**: Merchant-specific variant inventory
- **ProductVariantPrice**: Pricing per merchant
- **Order**: Customer orders
- **OrderItem**: Order line items
- **Coupon**: Discount codes
- **CouponUsage**: Coupon usage tracking

### Relationships

```
Product -> hasMany -> ProductVariant
ProductVariant -> hasMany -> UserProductVariant
UserProductVariant -> hasMany -> ProductVariantPrice
Order -> hasMany -> OrderItem
Order -> belongsTo -> Coupon
Coupon -> hasMany -> CouponUsage
```

## API Endpoints

Base URL: `{app_url}/api` (configurable via `commerce.route_prefix`)

### REST Resources

All resources support Lyre query string filters:

- `GET /api/products?with=variants,prices&search=widget&order=name,asc&per_page=20`
- `POST /api/products` - Create product
- `GET /api/products/{id}` - Get product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product
- `PUT /api/products/1,2,3` - Bulk update

Same pattern for: `locations`, `shippingaddresses`, `productvariants`, `userproductvariants`, `productvariantprices`, `orders`, `orderitems`, `coupons`, `couponusages`

### Cart Endpoints

#### Add Item to Cart
```http
POST /api/cart/add
Content-Type: application/json

{
  "product_variant_id": 1,
  "quantity": 2
}
```

#### Remove Item from Cart
```http
POST /api/cart/remove
Content-Type: application/json

{
  "product_variant_id": 1
}
```

#### Get Cart Summary
```http
GET /api/cart/summary
```

#### Apply Coupon
```http
POST /api/cart/apply-coupon
Content-Type: application/json

{
  "code": "DEMO10"
}
```

#### Remove Coupon
```http
POST /api/cart/remove-coupon
```

### Checkout Endpoints

#### Confirm Shipping
```http
POST /api/checkout/confirm-shipping
Content-Type: application/json

{
  "location_id": 1,
  "address_line_1": "123 Main St",
  "city": "Nairobi",
  "country": "Kenya"
}
```

Or use existing address:
```json
{
  "shipping_address_id": 1,
  "location_id": 1
}
```

#### Confirm Order
```http
POST /api/checkout/confirm-order
Content-Type: application/json

{
  "order_id": 1
}
```

#### Generate Invoice
```http
POST /api/checkout/invoice
Content-Type: application/json

{
  "order_id": 1
}
```

#### Pay (Mpesa STK Push)
```http
POST /api/checkout/pay
Content-Type: application/json

{
  "order_id": 1,
  "phone": "254712345678",
  "payment_term_reference": "prepaid"
}
```

## Services

### CartService

Handles cart operations:
- `add(array $payload)`: Add item to cart
- `remove(array $payload)`: Remove item
- `summary()`: Get cart summary
- `applyCoupon(string $code)`: Apply coupon
- `removeCoupon()`: Remove coupon

### PricingService

Computes order totals:
- `computeTotals(Order $order)`: Calculates amount, discounts, taxes, shipping

### CouponService

Validates coupons:
- `validateAndResolve(string $code)`: Validates code, dates, limits

### CheckoutService

Manages checkout flow:
- `confirmShipping(array $payload)`: Attach shipping address
- `confirmOrder(int|string $orderId)`: Confirm order
- `invoice(int|string $orderId)`: Generate invoice
- `pay(int|string $orderId, array $payload)`: Initiate payment

## Events & Observers

### Order Events

Emitted when order status changes:

- `OrderConfirmed`: Order status → confirmed
- `OrderInvoiced`: Order status → invoiced
- `OrderPaid`: Order status → paid
- `OrderReadyForFulfillment`: Order status → ready_for_fulfillment
- `OrderFulfilled`: Order status → fulfilled

### Listening to Events

```php
use Lyre\Commerce\Events\OrderReadyForFulfillment;

Event::listen(OrderReadyForFulfillment::class, function ($event) {
    $order = $event->order;
    // Trigger delivery workflow
});
```

## Filament Admin

All Commerce models have Filament resources:

- Navigate to `/admin/commerce`
- Resources: Products, Variants, Orders, Coupons, Locations, etc.
- Full CRUD operations
- Relationship management

## Testing

### Manual Testing

1. Seed demo data: `php artisan db:seed --class="Lyre\Commerce\Database\Seeders\CommerceDemoSeeder"`
2. Use demo Product Variant ID from seeder output
3. Test cart flow:
   - Add item → Check summary → Apply coupon → Confirm shipping → Invoice → Pay

### API Testing Script

See `docs/cursor/api-tests.sh` for cURL examples.

## Payment Integration

### Mpesa Flow

1. User initiates payment via `/api/checkout/pay`
2. System calls `MpesaClient::express(phone, amount)`
3. Mpesa sends STK push to user
4. User completes payment
5. Webhook updates Transaction status in Billing package
6. Order status updated to `paid` (via listener)

### Webhook Handling

Mpesa webhooks are handled by Billing package. To update Order status:

```php
// In webhook handler or Transaction observer
Event::listen('transaction.completed', function ($transaction) {
    // Find order by transaction metadata
    // Update order status to 'paid'
});
```

## Fulfillment Flow

### Order Statuses

1. `pending`: Initial cart state
2. `confirmed`: Shipping confirmed, reference generated
3. `invoiced`: Invoice generated, totals computed
4. `paid`: Payment completed (webhook)
5. `ready_for_fulfillment`: Ready for delivery (COD/postpaid)
6. `fulfilled`: Order delivered
7. `canceled`: Order cancelled

### Payment Term Behaviors

- **prepaid**: `invoiced` → `paid` → `ready_for_fulfillment`
- **cod**: `invoiced` → `ready_for_fulfillment` → `paid` (on delivery)
- **postpaid_net30**: `invoiced` → `ready_for_fulfillment` → `paid` (after 30 days)

## Query String Filters

All list endpoints support Lyre filters:

- `with=relation1,relation2` - Eager load relationships
- `search=keyword` - Search across serializable columns
- `filter=column,value` - Filter by column
- `range=column,from,to` - Filter by range
- `relation=relation,value` - Filter by related model
- `order=column,direction` - Sort results
- `per_page=20` - Pagination size
- `page=2` - Page number
- `startswith=prefix` - Filter by name prefix
- `withcount=relation` - Include relation counts
- `wherenull=column` - Filter nulls
- `doesnthave=relation` - Filter missing relations
- `random=true` - Random order
- `first=true` - Return first result only
- `unpaginated=true` - Disable pagination
- `limit=10` - Limit results
- `offset=20` - Skip results

## Troubleshooting

### Migrations Not Found

```bash
php artisan vendor:publish --tag=lyre-commerce-migrations --force
php artisan migrate
```

### Filament Resources Not Showing

Ensure plugin is registered in `AdminPanelProvider`:

```php
LyreCommerceFilamentPlugin::make()
```

### Cart Returns Empty

- Ensure user is authenticated
- Check if pending order exists for current user
- Verify product variants and prices exist

### Payment Not Processing

- Verify Mpesa credentials in Billing package
- Check webhook URL configuration
- Review transaction logs

## Next Steps

- Implement Delivery package integration
- Add inventory management
- Create reporting endpoints
- Add order history for users
- Implement refund workflow
