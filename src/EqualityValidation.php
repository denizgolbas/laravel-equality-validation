<?php

namespace DenizGolbas\LaravelEqualityValidation;

class EqualityValidation
{
    /**
     * Create a new equality validation rule instance.
     *
     * @param string $referenceModel
     * @param string $referenceColumn
     * @param string $targetModel
     * @param string $targetColumn
     * @param string $targetAttribute
     * @param bool $sameLine
     * @return EqualityValidationRule
     */
    public static function rule(
        string $referenceModel,
        string $referenceColumn,
        string $targetModel,
        string $targetColumn,
        string $targetAttribute,
        bool $sameLine = true
    ): EqualityValidationRule {
        return new EqualityValidationRule(
            $referenceModel,
            $referenceColumn,
            $targetModel,
            $targetColumn,
            $targetAttribute,
            $sameLine
        );
    }
}

