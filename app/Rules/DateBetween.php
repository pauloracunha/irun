<?php


namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class DateBetween
 * @package App\Rules
 */
class DateBetween implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $dates = explode(' AND ', $value);
        if(count($dates) < 2) {
            return false;
        }

        return preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return ":attribute deve estar no formato 'yyyy/mm/dd H:i:s AND yyyy/mm/dd H:i:s'! Ex.: 2021-02-15 00:00:00 AND 2021-02-16 23:59:59";
    }
}