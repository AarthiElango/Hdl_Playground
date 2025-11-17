<?php
namespace App\Helpers;

class FastSms
{

    private $key;

    public function __construct()
    {

        return $this->key = $_ENV['FASTSMS_API_KEY'];
    }

    public function send($uid)
    {

        $dbconn = new \App\Helpers\DB;

        $user = (object) $dbconn->db->get('users', ['mobile', 'otp'], ['id' => $uid]);

        $client = new \GuzzleHttp\Client();

        $url = sprintf(
            "https://www.fast2sms.com/dev/bulkV2?authorization=%s&variables_values=%s&route=otp&numbers=%s",
            $this->key,
            $user->otp,
            $user->mobile,
        );
        $response = $client->request('GET', $url);

        $status = $response->getStatusCode();

        return $status == 200;

    }

}
