<?php

namespace App\Core\Ai\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class PublicAiEndpointUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (! config('ai.block_private_endpoints', true)) {
            return;
        }

        $host = parse_url($value, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            $fail('The endpoint URL must include a valid host.');

            return;
        }

        $resolved = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        if (! filter_var($resolved, FILTER_VALIDATE_IP)) {
            return;
        }

        if (! filter_var(
            $resolved,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        )) {
            $fail('The endpoint URL must not target a private or reserved network address.');
        }
    }
}
