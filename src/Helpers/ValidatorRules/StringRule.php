<?php

namespace App\Helpers\ValidatorRules;

use Rakit\Validation\Rule;

class StringRule extends Rule
{
    protected $message = ":attribute is not a valid string";


    public function check($value): bool
    {
       
        return !!is_string($value);
    }
}
