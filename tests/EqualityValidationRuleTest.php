<?php

namespace DenizGolbas\LaravelEqualityValidation\Tests;

use DenizGolbas\LaravelEqualityValidation\EqualityValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;

class EqualityValidationRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tables
        Schema::create('reference_models', function ($table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('currency')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        Schema::create('target_models', function ($table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('currency')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            \DenizGolbas\LaravelEqualityValidation\EqualityValidationServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    // ============================================
    // TEST MATRIX: Success Cases (Passing Tests)
    // ============================================

    /**
     * Test Matrix: Success Cases
     */
    
    public function test_validation_passes_when_columns_match()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_numeric_values()
    {
        $reference = ReferenceModel::create(['code' => '123']);
        $target = TargetModel::create(['code' => '123']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_empty_strings()
    {
        $reference = ReferenceModel::create(['code' => '']);
        $target = TargetModel::create(['code' => '']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_different_column_names()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'DEFAULT', 'currency' => 'REF001']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'currency',
                    'target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_when_same_line_is_false()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id',
                    false
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_nested_attributes()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'items' => [
                [
                    'reference_id' => $reference->id,
                    'target_id' => $target->id,
                ],
            ],
        ], [
            'items.*.reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'items.*.target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_multiple_nested_items()
    {
        $reference1 = ReferenceModel::create(['code' => 'REF001']);
        $target1 = TargetModel::create(['code' => 'REF001']);
        $reference2 = ReferenceModel::create(['code' => 'REF002']);
        $target2 = TargetModel::create(['code' => 'REF002']);

        $validator = Validator::make([
            'items' => [
                [
                    'reference_id' => $reference1->id,
                    'target_id' => $target1->id,
                ],
                [
                    'reference_id' => $reference2->id,
                    'target_id' => $target2->id,
                ],
            ],
        ], [
            'items.*.reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'items.*.target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    // ============================================
    // TEST MATRIX: Failure Cases (Failing Tests)
    // ============================================

    public function test_validation_fails_when_columns_do_not_match()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF002']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('reference_id', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_case_sensitive_mismatch()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'ref001']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_with_whitespace_differences()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF001 ']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_with_different_column_names_mismatch()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'DEFAULT', 'currency' => 'REF002']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'currency',
                    'target_id'
                ),
            ],
        ]);

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_with_nested_attributes_mismatch()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF002']);

        $validator = Validator::make([
            'items' => [
                [
                    'reference_id' => $reference->id,
                    'target_id' => $target->id,
                ],
            ],
        ], [
            'items.*.reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'items.*.target_id'
                ),
            ],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('items.0.reference_id', $validator->errors()->toArray());
    }

    // ============================================
    // TEST MATRIX: Edge Cases
    // ============================================

    public function test_validation_passes_when_reference_model_not_found()
    {
        $target = TargetModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'reference_id' => 99999, // Non-existent ID
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        // Should pass because null models are skipped (let other rules handle existence)
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_when_target_model_not_found()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => 99999, // Non-existent ID
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        // Should pass because null models are skipped
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_when_both_models_not_found()
    {
        $validator = Validator::make([
            'reference_id' => 99999,
            'target_id' => 88888,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        // Should pass because null models are skipped
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_when_reference_id_is_null()
    {
        $target = TargetModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'reference_id' => null,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_when_target_id_is_null()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => null,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_when_column_value_is_null()
    {
        $reference = ReferenceModel::create(['code' => null]);
        $target = TargetModel::create(['code' => null]);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        // Both null, so isset check fails and validation passes
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_when_one_column_is_null()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => null]);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        // One is null, so isset check fails and validation passes
        $this->assertTrue($validator->passes());
    }

    // ============================================
    // TEST MATRIX: SameLine Parameter Tests
    // ============================================

    public function test_same_line_true_uses_nested_path()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'data' => [
                'reference_id' => $reference->id,
                'target_id' => $target->id,
            ],
        ], [
            'data.reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'data.target_id',
                    true // sameLine = true
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_same_line_false_uses_base_attribute()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'data' => [
                'reference_id' => $reference->id,
            ],
            'target_id' => $target->id,
        ], [
            'data.reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id',
                    false // sameLine = false
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    // ============================================
    // TEST MATRIX: Error Message Tests
    // ============================================

    public function test_error_message_contains_correct_placeholders()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF002']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertFalse($validator->passes());
        
        $errors = $validator->errors()->get('reference_id');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('ReferenceModel', $errors[0]);
        $this->assertStringContainsString('TargetModel', $errors[0]);
    }

    public function test_error_message_uses_column_names_when_no_translation()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF002']);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'target_id'
                ),
            ],
        ]);

        $this->assertFalse($validator->passes());
        
        $errors = $validator->errors()->get('reference_id');
        $this->assertStringContainsString('code', $errors[0]);
    }

    // ============================================
    // TEST MATRIX: Data Type Tests
    // ============================================

    public function test_validation_works_with_integer_status()
    {
        $reference = ReferenceModel::create(['status' => 1]);
        $target = TargetModel::create(['status' => 1]);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'status',
                    TargetModel::class,
                    'status',
                    'target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_different_integer_status()
    {
        $reference = ReferenceModel::create(['status' => 1]);
        $target = TargetModel::create(['status' => 2]);

        $validator = Validator::make([
            'reference_id' => $reference->id,
            'target_id' => $target->id,
        ], [
            'reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'status',
                    TargetModel::class,
                    'status',
                    'target_id'
                ),
            ],
        ]);

        $this->assertFalse($validator->passes());
    }

    // ============================================
    // TEST MATRIX: Complex Scenarios
    // ============================================

    public function test_validation_with_deeply_nested_structure()
    {
        $reference = ReferenceModel::create(['code' => 'REF001']);
        $target = TargetModel::create(['code' => 'REF001']);

        $validator = Validator::make([
            'order' => [
                'items' => [
                    [
                        'reference_id' => $reference->id,
                        'target_id' => $target->id,
                    ],
                ],
            ],
        ], [
            'order.items.*.reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'order.items.*.target_id'
                ),
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_with_mixed_valid_and_invalid_items()
    {
        $reference1 = ReferenceModel::create(['code' => 'REF001']);
        $target1 = TargetModel::create(['code' => 'REF001']);
        $reference2 = ReferenceModel::create(['code' => 'REF002']);
        $target2 = TargetModel::create(['code' => 'REF003']); // Mismatch

        $validator = Validator::make([
            'items' => [
                [
                    'reference_id' => $reference1->id,
                    'target_id' => $target1->id,
                ],
                [
                    'reference_id' => $reference2->id,
                    'target_id' => $target2->id,
                ],
            ],
        ], [
            'items.*.reference_id' => [
                new EqualityValidationRule(
                    ReferenceModel::class,
                    'code',
                    TargetModel::class,
                    'code',
                    'items.*.target_id'
                ),
            ],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('items.1.reference_id', $validator->errors()->toArray());
    }
}

// Test Models
class ReferenceModel extends Model
{
    protected $table = 'reference_models';
    protected $fillable = ['code', 'currency', 'status'];
}

class TargetModel extends Model
{
    protected $table = 'target_models';
    protected $fillable = ['code', 'currency', 'status'];
}

