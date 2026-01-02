# Laravel Equality Validation

[![Tests](https://github.com/denizgolbas/laravel-equality-validation/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/denizgolbas/laravel-equality-validation/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/denizgolbas/laravel-equality-validation.svg)](https://packagist.org/packages/denizgolbas/laravel-equality-validation)
[![Packagist Downloads](https://img.shields.io/packagist/dt/denizgolbas/laravel-equality-validation.svg)](https://packagist.org/packages/denizgolbas/laravel-equality-validation)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/denizgolbas/laravel-equality-validation.svg)](https://packagist.org/packages/denizgolbas/laravel-equality-validation)
[![Laravel Version](https://img.shields.io/badge/Laravel-10.x%20%7C%2011.x-red.svg)](https://laravel.com)

A powerful Laravel validation rule for checking equality between reference and target model columns. Perfect for validating relationships, matching codes, currencies, and ensuring data consistency across related models.

## Installation

You can install the package via Composer:

```bash
composer require denizgolbas/laravel-equality-validation
```

The package will automatically register its service provider.

## Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag=equality-validation-config
```

## Usage

This validation rule allows you to validate that a column value from a reference model matches a column value from a target model.

### Example 1: Basic Request Validation

```php
use Illuminate\Http\Request;
use DenizGolbas\LaravelEqualityValidation\EqualityValidationRule;
use App\Models\Order;
use App\Models\Invoice;

public function store(Request $request)
{
    $request->validate([
        'order_id' => [
            'required',
            'exists:orders,id',
            new EqualityValidationRule(
                Order::class,        // Reference model
                'code',              // Reference column
                Invoice::class,      // Target model
                'code',              // Target column
                'invoice_id'         // Target attribute name
            ),
        ],
        'invoice_id' => 'required|exists:invoices,id',
    ]);

    // Your logic here
}
```

### Example 2: Using FormRequest Class

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use DenizGolbas\LaravelEqualityValidation\EqualityValidationRule;
use App\Models\Order;
use App\Models\Invoice;

class CreateOrderInvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'exists:orders,id',
                new EqualityValidationRule(
                    Order::class,
                    'code',
                    Invoice::class,
                    'code',
                    'invoice_id'
                ),
            ],
            'invoice_id' => 'required|exists:invoices,id',
        ];
    }
}
```

### Example 3: Validating Warehouse and Product Location

```php
use Illuminate\Http\Request;
use DenizGolbas\LaravelEqualityValidation\EqualityValidationRule;
use App\Models\Warehouse;
use App\Models\Product;

public function transferProduct(Request $request)
{
    $request->validate([
        'from_warehouse_id' => [
            'required',
            'exists:warehouses,id',
            new EqualityValidationRule(
                Warehouse::class,
                'location_code',
                Product::class,
                'current_location',
                'product_id',
                false // sameLine = false, because product_id is at root level
            ),
        ],
        'product_id' => 'required|exists:products,id',
        'to_warehouse_id' => 'required|exists:warehouses,id',
    ]);

    // Transfer logic
}
```

### Example 4: Validating Multiple Items in Array

```php
use Illuminate\Http\Request;
use DenizGolbas\LaravelEqualityValidation\EqualityValidationRule;
use App\Models\Order;
use App\Models\Invoice;

public function createBulkInvoices(Request $request)
{
    $request->validate([
        'items.*.order_id' => [
            'required',
            'exists:orders,id',
            new EqualityValidationRule(
                Order::class,
                'code',
                Invoice::class,
                'code',
                'items.*.invoice_id'
            ),
        ],
        'items.*.invoice_id' => 'required|exists:invoices,id',
    ]);

    // Process bulk invoices
}
```

### Example 5: Validating User and Company Relationship

```php
use Illuminate\Http\Request;
use DenizGolbas\LaravelEqualityValidation\EqualityValidationRule;
use App\Models\User;
use App\Models\Company;

public function assignUserToCompany(Request $request)
{
    $request->validate([
        'user_id' => [
            'required',
            'exists:users,id',
            new EqualityValidationRule(
                User::class,
                'company_code',
                Company::class,
                'code',
                'company_id'
            ),
        ],
        'company_id' => 'required|exists:companies,id',
    ]);

    // Assignment logic
}
```

### Example 6: Validating Nested Data Structure

```php
use Illuminate\Http\Request;
use DenizGolbas\LaravelEqualityValidation\EqualityValidationRule;
use App\Models\Order;
use App\Models\Shipment;

public function createShipment(Request $request)
{
    $request->validate([
        'order.order_id' => [
            'required',
            'exists:orders,id',
            new EqualityValidationRule(
                Order::class,
                'tracking_code',
                Shipment::class,
                'order_tracking_code',
                'shipment.shipment_id'
            ),
        ],
        'shipment.shipment_id' => 'required|exists:shipments,id',
    ]);

    // Create shipment
}
```

### Example 7: Using Helper Method (Alternative Syntax)

```php
use Illuminate\Http\Request;
use DenizGolbas\LaravelEqualityValidation\EqualityValidation;
use App\Models\Order;
use App\Models\Invoice;

public function store(Request $request)
{
    $request->validate([
        'order_id' => [
            'required',
            'exists:orders,id',
            EqualityValidation::rule(
                Order::class,
                'code',
                Invoice::class,
                'code',
                'invoice_id'
            ),
        ],
        'invoice_id' => 'required|exists:invoices,id',
    ]);
}
```

### Parameters

The `EqualityValidationRule` constructor accepts the following parameters:

1. **`$referenceModel`** (string): The fully qualified class name of the reference model
2. **`$referenceColumn`** (string): The column name in the reference model to compare
3. **`$targetModel`** (string): The fully qualified class name of the target model
4. **`$targetColumn`** (string): The column name in the target model to compare
5. **`$targetAttribute`** (string): The attribute name in the request that contains the target model ID
6. **`$sameLine`** (bool, optional): Whether to use the same line/level for finding the target attribute. Defaults to `true`

### Same Line Parameter

When `$sameLine` is `true` (default), the rule will look for the target attribute at the same nesting level as the reference attribute. For example:

```php
// With sameLine = true (default)
[
    'items' => [
        ['order_id' => 1, 'invoice_id' => 2]
    ]
]
// Will look for 'items.*.invoice_id' when validating 'items.*.order_id'
```

When `$sameLine` is `false`, it will use the base attribute name:

```php
// With sameLine = false
[
    'order_id' => 1,
    'invoice_id' => 2
]
// Will look for 'invoice_id' when validating 'order_id'
```

### Example 8: Real-World E-Commerce Scenario

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use DenizGolbas\LaravelEqualityValidation\EqualityValidationRule;
use App\Models\Order;
use App\Models\Payment;

class ProcessPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'exists:orders,id',
                new EqualityValidationRule(
                    Order::class,
                    'currency',
                    Payment::class,
                    'currency',
                    'payment_id'
                ),
            ],
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Sipariş seçilmelidir.',
            'payment_id.required' => 'Ödeme bilgisi seçilmelidir.',
        ];
    }
}
```

### Example 9: Validating with Different Column Names

```php
use Illuminate\Http\Request;
use DenizGolbas\LaravelEqualityValidation\EqualityValidationRule;
use App\Models\Customer;
use App\Models\Account;

public function linkAccount(Request $request)
{
    $request->validate([
        'customer_id' => [
            'required',
            'exists:customers,id',
            new EqualityValidationRule(
                Customer::class,
                'region',           // Customer has 'region' column
                Account::class,
                'customer_region',  // Account has 'customer_region' column
                'account_id'
            ),
        ],
        'account_id' => 'required|exists:accounts,id',
    ]);
}
```

## Translation

The package includes English and Turkish translations. You can publish the language files to customize the error messages:

```bash
php artisan vendor:publish --tag=equality-validation-lang
```

This will publish the language files to `lang/vendor/equality-validation/{locale}/validation.php`.

After publishing, you can customize the error messages in the published files.

The default error message is:

**English:**
> The :reference_column of :reference_model does not match the :target_column of :target_model.

**Turkish:**
> :reference_model'in :reference_column değeri, :target_model'in :target_column değeri ile eşleşmiyor.

If you want to use the translations in your application's main language files instead, you can copy the validation key to your `lang/{locale}/validation.php` file under the `custom` key.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Author

Deniz Golbas - info@denizgolbas.com

