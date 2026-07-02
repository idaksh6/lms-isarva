<?php

namespace App\Rules;

use App\Support\ExternalSubmissionLink;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExternalSubmissionUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (! ExternalSubmissionLink::isAllowed($value)) {
            $fail('The :attribute must be a share link from Google Drive, Dropbox, or OneDrive.');
        }
    }
}
