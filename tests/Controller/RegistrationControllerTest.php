<?php

namespace App\Tests\Controller;

use App\Service\BillingClient;
use App\Tests;
use App\Tests\Mock\BillingClientMock;

class RegistrationControllerTest extends Tests\AbstractTest
{
    public function testRegisterAndLogout(): void
    {
        $client = static::GetClient();

        $client->disableReboot();

        static::getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock()
        );

        $crawler = $client->request('GET', LOCAL . '/courses/');
        $this->assertResponseOk();

        $crawler = $client->clickLink('Регистрация');

        $form = $crawler->selectButton('Зарегистрироваться')->form();

        $form['registration[email]'] = Tests\Mock\USERNAME_NEW_USER;
        $form['registration[password]'] = "password";
        $form['registration[passwordRepeat]'] = "password";

        $crawler = $client->submit($form);

        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();

        $this->assertResponseOk();

        $crawler = $client->clickLink('Профиль');
        $this->assertResponseOk();

        $email = $crawler->filter('p');
        $role = $email->nextAll();
        $balance = $role->nextAll();

        $this->assertEquals('Имейл: ' . Tests\Mock\USERNAME_NEW_USER, $email->text());
        $this->assertEquals('Роль: Пользователь', $role->text());
        $this->assertEquals('Баланс: ' . Tests\Mock\BALANCE_NEW_USER, $balance->text());

        $client->request('GET', LOCAL . '/courses/');
        $this->assertResponseOk();
        $client->clickLink('Выйти');
        $client->followRedirect();

        $this->assertResponseRedirect();
    }
}