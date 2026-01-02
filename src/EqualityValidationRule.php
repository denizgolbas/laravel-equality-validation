<?php

namespace DenizGolbas\LaravelEqualityValidation;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class EqualityValidationRule implements DataAwareRule, ValidationRule
{
    protected string $referenceModel;
    protected string $referenceColumn;
    protected string $targetModel;
    protected string $targetColumn;
    protected string $targetAttribute;
    protected bool $sameLine = true;

    protected array $data;

    public function setData(array $data): static
    {
        $this->data = Arr::dot($data);

        return $this;
    }

    public function __construct(
        string $referenceModel,
        string $referenceColumn,
        string $targetModel,
        string $targetColumn,
        string $targetAttribute,
        bool $sameLine = true
    ) {
        $this->referenceModel = $referenceModel;
        $this->referenceColumn = $referenceColumn;
        $this->targetModel = $targetModel;
        $this->targetColumn = $targetColumn;
        $this->targetAttribute = $targetAttribute;
        $this->sameLine = $sameLine;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $explodedReferenceAttribute = explode('.', $attribute);
        $realReferenceAttribute = end($explodedReferenceAttribute);

        $explodedTargetAttribute = explode('.', $this->targetAttribute);
        $realTargetAttribute = end($explodedTargetAttribute);

        $targetAttribute = str_replace($realReferenceAttribute, $realTargetAttribute, $attribute);

        $reference = $this->referenceModel::find($value ?? null);

        $target = $this->sameLine
            ? $this->targetModel::find($this->data[$targetAttribute] ?? null)
            : $this->targetModel::find($this->data[$realReferenceAttribute] ?? null);

        // If either model is not found, skip validation (let other rules handle existence)
        if (!$reference || !$target) {
            return;
        }

        if (isset($reference->{$this->referenceColumn}, $target->{$this->targetColumn})
            && $reference->{$this->referenceColumn} !== $target->{$this->targetColumn}
        ) {
            $referenceColumnLabel = __('validation.attributes.' . $this->referenceColumn);
            $targetColumnLabel = __('validation.attributes.' . $this->targetColumn);

            // Fallback to column name if translation not found
            if ($referenceColumnLabel === 'validation.attributes.' . $this->referenceColumn) {
                $referenceColumnLabel = $this->referenceColumn;
            }

            if ($targetColumnLabel === 'validation.attributes.' . $this->targetColumn) {
                $targetColumnLabel = $this->targetColumn;
            }

            // Get model names (fallback to class basename if no translation available)
            $referenceModelName = class_basename($this->referenceModel);
            $targetModelName = class_basename($this->targetModel);

            $fail(__('equality-validation::validation.custom.line_reference_columns_equality', [
                'reference_model' => $referenceModelName,
                'reference_column' => $referenceColumnLabel,
                'target_model' => $targetModelName,
                'target_column' => $targetColumnLabel,
            ]));
        }
    }
}
