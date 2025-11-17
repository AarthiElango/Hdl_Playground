<?php

namespace App\Helpers\ValidatorRules;

use Rakit\Validation\Rule;

class ExistsRule extends Rule
{
    protected $message = ":attribute does not exist";

    protected $fillableParams = ['table', 'column'];

    public function check($value): bool
    {
       
        $table = $this->parameter('table');
        $column = $this->parameter('column');

         $dbconn = new \App\Helpers\DB;

        $count = $dbconn->db->count($table, [$column => $value]);

        return !!$count;
    }
}
