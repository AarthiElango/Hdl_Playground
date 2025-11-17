<?php
namespace App\Helpers;

use Rakit\Validation\Validator as RakitValidator;
use App\Helpers\ValidatorRules\EnumRule;
use App\Helpers\ValidatorRules\UniqueRule;
use App\Helpers\ValidatorRules\StringRule;
use App\Helpers\ValidatorRules\ExistsRule;


class Validator
{
    public $validData = NULL;

    public function make($data, $rules, $messages = [])
    {
        $validator = new RakitValidator;

        $validator->addValidator('enum', new EnumRule());
        $validator->addValidator('unique', new UniqueRule());
        $validator->addValidator('string', new StringRule());
        $validator->addValidator('exists', new ExistsRule());

      
        $validation = $validator->make($data, $rules);

        if(!empty($messages)){
            $validation->setMessages($messages);
        }

        $validation->validate();

        if($validation->fails()){

            $errors = $validation->errors();

            return $errors->all();

        }

        $this->validData = (object) $validation->getValidData();

        return true;
    }
}
