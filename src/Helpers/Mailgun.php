<?php
namespace App\Helpers;

use Mailgun\Mailgun as MailgunSdk;

class Mailgun
{

    private $key;

    private $domain;

    private $mg;

    public function __construct()
    {

        $this->key = $_ENV['MAILGUN_KEY'];                                                            // $this->mg =  Mailgun::create('key-example'); // For US servers
        $this->domain = $_ENV['MAILGUN_DOMAIN'];                                                            // $this->mg =  Mailgun::create('key-example'); // For US servers
        $this->mg = MailgunSdk::create($this->key); // For EU servers
    }

    public function send($args)
    {

        if(empty($args['from'])){

            $args['from'] = 'postmaster@'.$this->domain;
        }

        if(empty($args['to'])){

            return 'To address is not available';
        }

        try{
            $sent =  $this->mg->messages()->send($this->domain, $args);

            return $sent;

        }catch(Exception $e){

            return false;
        }
      
    }
}
