<?php

namespace App\Tests\Mock;

use App\Service\BillingClient;
use PHPUnit\Util\Exception;

const TOKEN_ADMIN = "SUPER_ADMIN_TOKEN";
const USERNAME_ADMIN = "admin@mail.ru";
const PASSWORD_ADMIN = "adminPassword";
const BALANCE_ADMIN = 5000.0;
const TOKEN_USER = "USER_TOKEN";
const USERNAME_USER = "user@mail.ru";
const PASSWORD_USER = "userPassword";
const BALANCE_USER = 1000.0;

const INVALIDATE_EMAIL = "1111@MAILRU";
const REPEAT_EMAIL = "1111@MAILRU";
const USERNAME_NEW_USER = "newUser@mail.ru";
const BALANCE_NEW_USER = 0.0;
const TOKEN_NEW_USER = "NEW_USER_TOKEN";


class BillingClientMock extends BillingClient
{

    public function registration(string $username, string $password)
    {
        if ($username === INVALIDATE_EMAIL) {
            return "Некорректный имейл";
        }

        if ($username === REPEAT_EMAIL) {
            return "Данный имейл уже зарегистрирован";
        }

        if ($username === USERNAME_NEW_USER) {
            return TOKEN_NEW_USER;
        }


        return "Ошибка";
    }

    public function authentication(string $username, string $password): string
    {
        if (($username === USERNAME_USER) && ($password === PASSWORD_USER)) {
            return TOKEN_USER;
        }

        if (($username === USERNAME_ADMIN) && ($password === PASSWORD_ADMIN)) {
            return TOKEN_ADMIN;
        }

        return "Данные некорректны";
    }

    public function getCurrentUser(string $token)
    {
        if ($token === TOKEN_USER) {
            return [
                "username" => USERNAME_USER,
                "roles" => [
                    "ROLES_USER"
                ],
                "balance" => BALANCE_USER,
            ];
        }

        if ($token === TOKEN_ADMIN) {
            return [
                "username" => USERNAME_ADMIN,
                "roles" => [
                    "ROLE_SUPER_ADMIN",
                    "ROLE_USER"
                ],
                "balance" => BALANCE_ADMIN,
            ];
        }

        if ($token === TOKEN_NEW_USER) {
            return [
                "username" => USERNAME_NEW_USER,
                "roles" => [
                    "ROLE_USER",
                ],
                "balance" => BALANCE_NEW_USER,
            ];
        }

        return "Токен некорректный";
    }
}
