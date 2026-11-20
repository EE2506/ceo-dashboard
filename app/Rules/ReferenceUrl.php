<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ReferenceUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) !== $value || ! filter_var($value, FILTER_VALIDATE_URL)) {
            $fail('Enter a valid HTTPS reference link.');

            return;
        }

        $parts = parse_url($value);

        if (
            strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || $this->containsCredentialKey($parts['query'] ?? '')
            || $this->containsCredentialKey($parts['fragment'] ?? '')
        ) {
            $fail('Use an HTTPS reference link without embedded credentials or access tokens.');
        }
    }

    private function containsCredentialKey(string $parameters): bool
    {
        return preg_match(
            '/(?:^|[?&;])(?:[\w.-]*(?:token|password|secret|signature|credential|api[-_]?key)|authorization|sig|code)(?:=|\[)/i',
            rawurldecode($parameters),
        ) === 1;
    }
}
