# Testing Guide

## Overview

This project includes comprehensive test coverage for the coupon management system, including feature tests and unit tests.

## Running Tests

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit
```

### Run Specific Test File

```bash
php artisan test tests/Feature/CouponTest.php
php artisan test tests/Unit/CouponModelTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter test_user_can_create_coupon
```

### With Coverage (requires Xdebug or PCOV)

```bash
php artisan test --coverage
```

## Test Structure

### Feature Tests (`tests/Feature/`)

Feature tests test the complete user workflow and API endpoints:

**CouponTest.php** - Tests coupon CRUD operations:
- `test_coupon_index_page_can_be_rendered()` - Verifies coupon list page loads
- `test_coupon_create_page_can_be_rendered()` - Verifies create form loads
- `test_user_can_create_coupon()` - Tests coupon creation workflow
- `test_coupon_creation_validates_required_fields()` - Tests validation
- `test_coupon_creation_normalizes_phone_number()` - Tests phone normalization
- `test_coupon_show_page_can_be_rendered()` - Tests detail page
- `test_coupon_can_be_deleted()` - Tests deletion
- `test_coupon_index_filters_by_status()` - Tests status filtering
- `test_coupon_index_searches_by_code()` - Tests search by code
- `test_coupon_index_searches_by_customer_name()` - Tests search by name
- `test_public_coupon_view_can_be_accessed_without_auth()` - Tests public access
- `test_public_coupon_view_returns_404_for_invalid_code()` - Tests error handling
- `test_coupon_index_paginates_results()` - Tests pagination

### Unit Tests (`tests/Unit/`)

Unit tests test individual components in isolation:

**CouponModelTest.php** - Tests Coupon model:
- Phone normalization from various formats (08xx, +62, 62)
- Phone formatting for display
- Model scopes (active, used, expired)
- Model relationships (user, validations)
- Validation logic (`canBeValidated()`)

**CouponCodeGenerationTest.php** - Tests code generation:
- Code format validation (ABC-1234-XYZ)
- Code uniqueness
- Code length validation
- Code structure validation

## Writing New Tests

### Feature Test Example

```php
public function test_example_feature()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/coupons');
    
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('coupons/Index')
    );
}
```

### Unit Test Example

```php
public function test_example_unit()
{
    $coupon = Coupon::factory()->create([
        'status' => Coupon::STATUS_ACTIVE,
    ]);
    
    $this->assertTrue($coupon->canBeValidated());
}
```

## Test Data Factories

### CouponFactory

```php
Coupon::factory()->create(); // Random coupon
Coupon::factory()->active()->create(); // Active coupon
Coupon::factory()->used()->create(); // Used coupon
Coupon::factory()->expired()->create(); // Expired coupon
```

### CouponValidationFactory

```php
CouponValidation::factory()->create(); // Random validation
CouponValidation::factory()->used()->create(); // Used action
CouponValidation::factory()->reversed()->create(); // Reversed action
```

## Test Database

Tests use an in-memory SQLite database (configured in `phpunit.xml`). Each test runs in a transaction that is rolled back after completion, ensuring test isolation.

## Best Practices

1. **Use Factories**: Always use factories to create test data
2. **Test Isolation**: Each test should be independent
3. **Clear Names**: Use descriptive test method names
4. **Arrange-Act-Assert**: Follow AAA pattern
5. **Test Edge Cases**: Include tests for error conditions
6. **Keep Tests Fast**: Avoid unnecessary database operations

## Common Assertions

### Inertia Assertions

```php
$response->assertInertia(fn ($page) => 
    $page->component('coupons/Index')
        ->has('coupons')
        ->where('coupons.total', 10)
);
```

### Database Assertions

```php
$this->assertDatabaseHas('coupons', [
    'code' => 'ABC-1234-XYZ',
]);

$this->assertDatabaseMissing('coupons', [
    'id' => 999,
]);
```

### Model Assertions

```php
$this->assertInstanceOf(User::class, $coupon->user);
$this->assertTrue($coupon->canBeValidated());
```

## Troubleshooting

### Tests Failing Due to Missing Data

Ensure factories are properly set up and migrations have been run:

```bash
php artisan migrate --env=testing
```

### Inertia Assertions Not Working

Make sure you're using the correct component path (without `.vue` extension).

### Factory Errors

Ensure model factories are properly registered in the model's `newFactory()` method.
