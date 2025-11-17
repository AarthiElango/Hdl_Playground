<?php

namespace App\Helpers\ValidatorRules;

use Rakit\Validation\Rule;

class EnumRule extends Rule
{
    protected $message = ":attribute does not match";

    protected $fillableParams = ['values'];

    public function check($value): bool
    {
       
        $values = $this->parameter('values');

        $exploded = explode(';', $values);

        return in_array(strtolower($value), $exploded);
    }
}
