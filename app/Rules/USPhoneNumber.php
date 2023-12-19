<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class USPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Validate that the phone number matches the US format (###) ###-####
        return preg_match('/^\(\d{3}\) \d{3}-\d{4}$/', $value);
    }

    public function message()
    {
        return 'The :attribute must be a valid US phone number in the format (###) ###-####.';
    }
}
