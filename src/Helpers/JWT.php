<?php
namespace App\Helpers;

use Carbon\Carbon;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

class JWT
{
    private $key;

    public function __construct()
    {
        $this->key = getenv('JWT_SECRET') ?: 'default_secret_key';
    }

    public function encode(array $data): string
    {
        $issuedAt       = time();
        $expirationTime = $issuedAt + (int) $_ENV['JWT_EXPIRATION']; // JWT expiration time in seconds

        $payload = [
            'iat'  => $issuedAt,
            'exp'  => $expirationTime,
            'data' => $data,
        ];

        return FirebaseJWT::encode($payload, $this->key, 'HS256');
    }

    public function decode($token)
    {

        try {

            $decoded = FirebaseJWT::decode($token, new Key($this->key, 'HS256'));

            return $decoded;

        } catch (\Exception $e) {

            return $e->getMessage();
        }

    }

    public function getUid($request)
    {

        $authHeader = $request->getHeaderLine('Authorization');

        // Remove "Bearer " and trim spaces
        $token = null;
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (empty($token)) {

            return false;
        }

        $decoded = $this->decode($token);

        if (is_string($decoded)) {

            return $decoded;
        }

        if (empty($decoded->data->username)) {

            return "Unidentified user";
        }

        $dbconn = new \App\Helpers\DB;

        $row = $dbconn->db->get('users', 'id', ['username' => $decoded->data->username]);

        return intval($row);

    }

   
}
