<?php 

namespace App\Helpers;

class Credits{


    public function get_credits_by_amount($amount){

         if($amount == 10){

            return 1;
        }

        if($amount == 100){

            return 10;
        }
        
        if($amount == 500){

            return 60;
        }

        
        if($amount == 1000){

            return 150;
        }

        return 0;
    }
}