<?php

namespace App\Tests\Mock;

use App\Service\BillingClient;
use App\Tests\Service\GenerateJWTFromTests;
use App\Tests\Service\WorkWithTransactions;
use PHPUnit\Util\Exception;

const REFRESH_TOKEN_ADMIN = "SUPER_ADMIN_REFRESH_TOKEN";
const USERNAME_ADMIN = "admin@mail.ru";
const PASSWORD_ADMIN = "adminPassword";
const BALANCE_ADMIN = 5000.0;
const REFRESH_TOKEN_USER = "USER_REFRESH_TOKEN";
const USERNAME_USER = "user@mail.ru";
const PASSWORD_USER = "userPassword";
const BALANCE_USER = 1000.0;

const INVALIDATE_EMAIL = "1111@MAILRU";
const REPEAT_EMAIL = "1111@MAILRU";
const USERNAME_NEW_USER = "newUser@mail.ru";
const BALANCE_NEW_USER = 0.0;
const REFRESH_TOKEN_NEW_USER = "NEW_USER_REFRESH_TOKEN";

const CHARACTER_CODE_STRIZHKA = "kursy_po_strizhke";
const CHARACTER_CODE_BEG = "kursy_po_begu";
const CHARACTER_CODE_PLAVANIYU = "kursy_po_plavaniyu";
const CHARACTER_CODE_PRYZHKAM = "kursy_po_pryzhkam";

const TYPE_STRIZHKA = "rent";
const TYPE_BEG = "rent";
const TYPE_PLAVANIYU = "buy";
const TYPE_PRYZHKAM = "free";

const PRICE_STRIZHKA = 499.99;
const PRICE_BEG = 199.50;
const PRICE_PLAVANIYU = 750.66;
const PRICE_PRYZHKAM = null;

const TRANSACTIONS = [
    [
        'id' => 1,
        'created_at' => '2022-01-20T12:45:11+00:00',
        'type' => 'deposit',
        'amount' => 5000.00
    ],
    [
        'id' => 2,
        'created_at' => '2022-01-20T12:55:11+00:00',
        'type' => 'deposit',
        'amount' => 5000.00
    ],
    [
        'id' => 3,
        'created_at' => '2022-01-20T12:54:11+00:00',
        'type' => 'deposit',
        'amount' => 5000.00
    ],
    [
        'id' => 4,
        'created_at' => '2022-01-20T00:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_STRIZHKA,
        'amount' => PRICE_STRIZHKA,
    ],
    [
        'id' => 5,
        'created_at' => '2022-01-20T00:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_STRIZHKA,
        'amount' => PRICE_STRIZHKA,
    ],
    [
        'id' => 6,
        'created_at' => '2022-01-21T00:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_BEG,
        'amount' => PRICE_BEG,
    ],
    [
        'id' => 7,
        'created_at' => '2022-01-22T00:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_PLAVANIYU,
        'amount' => PRICE_PLAVANIYU,
    ],
    [
        'id' => 8,
        'created_at' => '2022-01-22T00:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_BEG,
        'amount' => PRICE_BEG,
    ],
];

const TRANSACTION_USER = [
    [
        'id' => 1,
        'created_at' => '2022-01-20T12:45:11+00:00',
        'type' => 'deposit',
        'amount' => 5000.00
    ],
    [
        'id' => 4,
        'created_at' => '2022-07-19T05:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_STRIZHKA,
        'amount' => PRICE_STRIZHKA,
    ],
    [
        'id' => 8,
        'created_at' => '2022-01-22T00:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_BEG,
        'amount' => PRICE_BEG,
    ],
];

const TRANSACTION_ADMIN = [
    [
        'id' => 2,
        'created_at' => '2022-01-20T12:55:11+00:00',
        'type' => 'deposit',
        'amount' => 5000.00
    ],
];

const TRANSACTION_NEW_USER = [
    [
        'id' => 3,
        'created_at' => '2022-01-20T12:54:11+00:00',
        'type' => 'deposit',
        'amount' => 5000.00
    ],
    [
        'id' => 5,
        'created_at' => '2022-01-20T00:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_STRIZHKA,
        'amount' => PRICE_STRIZHKA,
    ],
    [
        'id' => 6,
        'created_at' => '2022-01-21T00:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_BEG,
        'amount' => PRICE_BEG,
    ],
    [
        'id' => 7,
        'created_at' => '2022-01-22T00:00:00+00:00',
        'type' => 'payment',
        'course_code' => CHARACTER_CODE_PLAVANIYU,
        'amount' => PRICE_PLAVANIYU,
    ],
];

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
            $service = new GenerateJWTFromTests();
            $token = $service->generateJWT(USERNAME_NEW_USER);

            return [
                'token' => $token,
                'refresh_token' => REFRESH_TOKEN_NEW_USER,
            ];
        }

        return "Ошибка";
    }

    public function authentication(string $username, string $password)
    {
        $service = new GenerateJWTFromTests();

        if (($username === USERNAME_USER) && ($password === PASSWORD_USER)) {
            $token = $service->generateJWT(USERNAME_USER);

            return [
                'token' => $token,
                'refresh_token' => REFRESH_TOKEN_USER,
            ];
        }

        if (($username === USERNAME_ADMIN) && ($password === PASSWORD_ADMIN)) {
            $token = $service->generateJWT(USERNAME_ADMIN);

            return [
                'token' => $token,
                'refresh_token' => REFRESH_TOKEN_ADMIN,
            ];
        }

        return "Данные некорректны";
    }

    public function getCurrentUser(string $token)
    {
        $service = new GenerateJWTFromTests();
        $username = $service->getUsername($token);

        if ($username === USERNAME_USER) {
            return [
                "username" => USERNAME_USER,
                "roles" => [
                    "ROLES_USER"
                ],
                "balance" => BALANCE_USER,
            ];
        }

        if ($username === USERNAME_ADMIN) {
            return [
                "username" => USERNAME_ADMIN,
                "roles" => [
                    "ROLE_SUPER_ADMIN",
                    "ROLE_USER"
                ],
                "balance" => BALANCE_ADMIN,
            ];
        }

        if ($username === USERNAME_NEW_USER) {
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

    public function refreshToken(string $refreshToken)
    {
        $service = new GenerateJWTFromTests();

        if ($refreshToken == REFRESH_TOKEN_ADMIN) {
            $token = $service->generateJWT(USERNAME_ADMIN);

            return [
                "token" => $token,
                "refresh_token" => REFRESH_TOKEN_ADMIN
            ];
        }

        if ($refreshToken == REFRESH_TOKEN_USER) {
            $token = $service->generateJWT(USERNAME_USER);

            return [
                "token" => $token,
                "refresh_token" => REFRESH_TOKEN_USER
            ];
        }

        if ($refreshToken == REFRESH_TOKEN_NEW_USER) {
            $token = $service->generateJWT(USERNAME_NEW_USER);

            return [
                "token" => $token,
                "refresh_token" => REFRESH_TOKEN_NEW_USER
            ];
        }

        return "Токен обновления некорректный";
    }

    public function getDataAllCourses()
    {
        return [
            [
                'code' => CHARACTER_CODE_STRIZHKA,
                'type' => TYPE_STRIZHKA,
                "price" => PRICE_STRIZHKA,

            ],
            [
                'code' => CHARACTER_CODE_BEG,
                'type' => TYPE_BEG,
                "price" => PRICE_BEG,

            ],
            [
                'code' => CHARACTER_CODE_PLAVANIYU,
                'type' => TYPE_PLAVANIYU,
                "price" => PRICE_PLAVANIYU,

            ],
            [
                'code' => CHARACTER_CODE_PRYZHKAM,
                'type' => TYPE_PRYZHKAM,
            ],
        ];
    }

    public function getConcreteCourse(string $characterCode)
    {
        if ($characterCode == CHARACTER_CODE_STRIZHKA) {
            return [
                'code' => CHARACTER_CODE_STRIZHKA,
                'type' => TYPE_STRIZHKA,
                "price" => PRICE_STRIZHKA,
            ];
        }
        if ($characterCode == CHARACTER_CODE_BEG) {
            return [
                'code' => CHARACTER_CODE_BEG,
                'type' => TYPE_BEG,
                "price" => PRICE_BEG,
            ];
        }
        if ($characterCode == CHARACTER_CODE_PLAVANIYU) {
            return [
                'code' => CHARACTER_CODE_PLAVANIYU,
                'type' => TYPE_PLAVANIYU,
                "price" => PRICE_PLAVANIYU,
            ];
        }
        if ($characterCode == CHARACTER_CODE_PRYZHKAM) {
            return [
                'code' => CHARACTER_CODE_PRYZHKAM,
                'type' => TYPE_PRYZHKAM,
            ];
        }

        return 'Данного курса не существует';
    }

    public function payForTheCourse(string $characterCode, string $token)
    {
        if ((($token == TOKEN_USER) || ($token == TOKEN_ADMIN) || ($token == TOKEN_NEW_USER)) && ($characterCode == CHARACTER_CODE_STRIZHKA)) {
            $until = new \DateTime("now", new \DateTimeZone('UTC'));
            $until->modify('+7 day');

            return [
                'success' => true,
                'course_type' => TYPE_STRIZHKA,
                'expires_at' => $until->format('Y-m-d\TH:i:sP'),
            ];
        }

        if ((($token == TOKEN_USER) || ($token == TOKEN_ADMIN) || ($token == TOKEN_NEW_USER)) && ($characterCode == CHARACTER_CODE_BEG)) {
            $until = new \DateTime("now", new \DateTimeZone('UTC'));
            $until->modify('+7 day');

            return [
                'success' => true,
                'course_type' => TYPE_BEG,
                'expires_at' => $until->format('Y-m-d\TH:i:sP'),
            ];
        }

        if ((($token == TOKEN_USER) || ($token == TOKEN_ADMIN) || ($token == TOKEN_NEW_USER)) && ($characterCode == CHARACTER_CODE_PLAVANIYU)) {
            $until = new \DateTime("now", new \DateTimeZone('UTC'));
            $until->modify('+7 day');

            return [
                'success' => true,
                'course_type' => TYPE_PLAVANIYU,
                'expires_at' => null,
            ];
        }

        return [
            "code" => 406,
            "message" => "На вашем счету недостаточно средств",
        ];
    }

    public function getTransactions(string $token, array $filter = null)
    {
        $serviceJWT = new GenerateJWTFromTests();
        $username = $serviceJWT->getUsername($token);

        $serviceTransaction = new WorkWithTransactions();

        if ($username == USERNAME_USER) {
            $transactions = $serviceTransaction->updateCreateDate(TRANSACTION_USER);

            $transactions = $serviceTransaction->getAllTransactionByUser($transactions, $filter);

            return $transactions;
        }
        if ($username == USERNAME_ADMIN) {
            $transactions = $serviceTransaction->getAllTransactionByUser(TRANSACTION_ADMIN, $filter);

            return $transactions;
        }
        if ($username == USERNAME_NEW_USER) {
            $transactions = $serviceTransaction->updateCreateDate(TRANSACTION_NEW_USER);

            $transactions = $serviceTransaction->getAllTransactionByUser($transactions, $filter);

            return $transactions;
        }

        return [];
    }
}
