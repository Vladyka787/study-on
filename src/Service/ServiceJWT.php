<?php

namespace App\Service;

const TIME_LEFT = 300; // 5 минут

class ServiceJWT
{
    public function getEndTime($token)
    {
        if ($token !== null) {
            $data = explode('.', $token);
            $payload = json_decode(base64_decode($data[1]));
            $time = $payload->exp;

            return $time;
        }
        return 0;
    }

    public function updateCheck($time)
    {
        $now = time();
        if ($now > ($time - TIME_LEFT)) {
            return true;
        }

        return false;
    }
}
