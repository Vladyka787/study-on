<?php

namespace App\Tests\Service;

use App\Tests\Mock\BillingClientMock;
use const App\Tests\Mock\USERNAME_NEW_USER;

class GenerateJWTFromTests
{
    public function generateJWT(string $username): string
    {
        $objHeader = new \stdClass();
        $objHeader->typ = 'JWT';
        $objPayload = new \stdClass();
        $objPayload->exp = strtotime('+7 week');
        $objPayload->username = $username;
        $objVerify = new \stdClass();
        $objVerify->protection = 'off';

        $header = base64_encode(json_encode($objHeader, JSON_THROW_ON_ERROR));
        $payload = base64_encode(json_encode($objPayload, JSON_THROW_ON_ERROR));
        $verify = base64_encode(json_encode($objVerify, JSON_THROW_ON_ERROR));

        return $header . '.' . $payload . '.' . $verify;
    }

    public function getUsername($token)
    {
        $data = explode('.', $token);
        $payload = json_decode(base64_decode($data[1]));
        $username = $payload->username;

        return $username;

    }
}