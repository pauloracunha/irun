<?php

namespace App\Rules;

use App\Helpers\Generator;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class Cpf
 * @package App\Rules
 */
class Cpf implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Generator::validateCpf($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The CPF number is not valid!';
    }
}
