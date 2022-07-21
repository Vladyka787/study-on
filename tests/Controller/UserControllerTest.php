<?php

namespace App\Tests\Controller;

use App\Security\User;
use App\Tests;
use App\Tests\Mock\BillingClientMock;
use const App\Tests\Mock\REFRESH_TOKEN_NEW_USER;
use const App\Tests\Mock\USERNAME_NEW_USER;

class UserControllerTest extends Tests\AbstractTest
{
    public function testHistoryTransaction(): void
    {
        $client = static::GetClient();

        $client->disableReboot();

        static::getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock()
        );

        $service = new Tests\Service\GenerateJWTFromTests();

        $user = new User();
        $user->setEmail(USERNAME_NEW_USER);
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken($service->generateJWT(USERNAME_NEW_USER));
        $user->setApiRefreshToken(REFRESH_TOKEN_NEW_USER);

        $client->loginUser($user);
        $crawler = $client->request('GET', LOCAL . '/profile');
        $this->assertResponseOk();

        $crawler = $client->clickLink('История транзакций');

        $this->assertResponseOk();

        $line = $crawler->filter('body > div > table > tbody > tr');

        $this->assertEquals(4, $line->count());
    }
}