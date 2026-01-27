#!/bin/bash

# Lyre Commerce API Test Script
# Requires: curl, jq (optional), valid API key

BASE_URL="${BASE_URL:-http://nipate.test}"
API_KEY="${API_KEY:-your-api-key-here}"

echo "=== Lyre Commerce API Tests ==="
echo "Base URL: $BASE_URL"
echo ""

# Test 1: List Products
echo "1. Testing GET /api/products"
curl -s -H "X-API-KEY: $API_KEY" "$BASE_URL/api/products" | jq '.' || echo "Response received"
echo ""

# Test 2: Add to Cart
echo "2. Testing POST /api/cart/add"
curl -s -X POST \
  -H "X-API-KEY: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"product_variant_id": 1, "quantity": 2}' \
  "$BASE_URL/api/cart/add" | jq '.' || echo "Response received"
echo ""

# Test 3: Get Cart Summary
echo "3. Testing GET /api/cart/summary"
curl -s -H "X-API-KEY: $API_KEY" "$BASE_URL/api/cart/summary" | jq '.' || echo "Response received"
echo ""

# Test 4: Apply Coupon
echo "4. Testing POST /api/cart/apply-coupon"
curl -s -X POST \
  -H "X-API-KEY: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"code": "DEMO10"}' \
  "$BASE_URL/api/cart/apply-coupon" | jq '.' || echo "Response received"
echo ""

# Test 5: Confirm Shipping
echo "5. Testing POST /api/checkout/confirm-shipping"
curl -s -X POST \
  -H "X-API-KEY: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"location_id": 1, "address_line_1": "123 Test St"}' \
  "$BASE_URL/api/checkout/confirm-shipping" | jq '.' || echo "Response received"
echo ""

echo "=== Tests Complete ==="
echo ""
echo "Note: Set BASE_URL and API_KEY environment variables to customize:"
echo "  BASE_URL=http://nipate.test API_KEY=your-key bash api-tests.sh"
