<?php

declare(strict_types=1);

namespace Brigmaster\Http\Rest\Validation;

/**
 * Low-level request value predicates shared by EstimateController (request coercion)
 * and EstimateRequestValidator (validation). Extracted verbatim — pure and stateless.
 */
trait RequestValueHelpers
{
    private function isNonEmptyString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    private function isNumericValue(mixed $value): bool
    {
        return (is_string($value) && trim($value) !== '' && is_numeric($value)) || is_int($value) || is_float($value);
    }
}
