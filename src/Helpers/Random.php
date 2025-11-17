<?php
namespace App\Helpers;

class Random
{

    public function random($characters, $length = 6)
    {

        $charactersLength = strlen($characters);
        $randomString     = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function string($length = 6)
    {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return $this->random($characters, $length);
    }

    public function otp($length = 6)
    {
        if ($length < 1) {
            return '';
        }

        // First character cannot be 0
        $firstChars = '123456789';
        $otherChars = '0123456789';

        $otp = $firstChars[random_int(0, strlen($firstChars) - 1)];

        for ($i = 1; $i < $length; $i++) {
            $otp .= $otherChars[random_int(0, strlen($otherChars) - 1)];
        }

        return $otp;

    }

     public function slug($title, $suffix_length)
    {
        // Convert to lowercase
        $slug = strtolower($title);

        // Replace any non letter or digit with hyphen
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);

        // Trim hyphens from start and end
        $slug = trim($slug, '-');

        // Collapse multiple hyphens into one
        $slug = preg_replace('/-+/', '-', $slug);

        $suffix = strtolower($this->string($suffix_length));

        return $slug . '-' . $suffix;
    }

    function camelToSnake($input)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }

    function snakeToCamel($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }
}
