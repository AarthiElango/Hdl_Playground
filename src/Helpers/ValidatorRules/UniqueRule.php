<?php

namespace App\Helpers\ValidatorRules;

use Rakit\Validation\Rule;
use App\Models\Model;

class UniqueRule extends Rule
{
    protected $message = ":attribute taken already";

    protected $fillableParams = ['table', 'column', 'omitColumn', 'omitValue'];

    public function check($value): bool
    {
       
        $table = $this->parameter('table');

        $column = $this->parameter('column');

        $omitColumn = $this->parameter('omitColumn');

        $omitValue = $this->parameter('omitValue');

          $dbconn = new \App\Helpers\DB;


        $where = [$column => $value];

        if(!empty($omitColumn) && !empty($omitValue)){

            $where[$omitColumn.'[!]'] = $omitValue; 
        }

        $count = $dbconn->db->count($table, $where);

        return !$count;
    }
}
