<?php

namespace App\Tests\Controller;

use App\Service\BillingClient;
use App\Tests;
use App\Tests\Mock\BillingClientMock;

class LoginControllerTest extends Tests\AbstractTest
{
    public function testLoginAndProfileAndLogout(): void
    {
        $client = static::GetClient();

        $client->disableReboot();

        static::getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock()
        );
//Пользователь
        $crawler = $client->request('GET', LOCAL . '/courses/');

        $this->assertResponseOk();

        $crawler = $client->clickLink('Войти');

        $form = $crawler->selectButton('Войти')->form();


        $form['_remember_me']->tick();
        $form['email'] = Tests\Mock\USERNAME_USER;
        $form['password'] = Tests\Mock\PASSWORD_USER;

        $crawler = $client->submit($form);

        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();

        $this->assertResponseOk();

        $crawler = $client->clickLink('Профиль');
        $this->assertResponseOk();

        $email = $crawler->filter('p');
        $role = $email->nextAll();
        $balance = $role->nextAll();

        $this->assertEquals('Имейл: ' . Tests\Mock\USERNAME_USER, $email->text());
        $this->assertEquals('Роль: Пользователь', $role->text());
        $this->assertEquals('Баланс: ' . Tests\Mock\BALANCE_USER, $balance->text());

        $client->request('GET', LOCAL . '/courses/');
        $this->assertResponseOk();
        $client->clickLink('Выйти');
        $client->followRedirect();

        $this->assertResponseRedirect();

//Админ
        $crawler = $client->request('GET', LOCAL . '/courses/');

        $this->assertResponseOk();

        $crawler = $client->clickLink('Войти');

        $form = $crawler->selectButton('Войти')->form();


        $form['_remember_me']->tick();
        $form['email'] = Tests\Mock\USERNAME_ADMIN;
        $form['password'] = Tests\Mock\PASSWORD_ADMIN;

        $crawler = $client->submit($form);

        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();

        $this->assertResponseOk();

        $crawler = $client->clickLink('Профиль');
        $this->assertResponseOk();

        $email = $crawler->filter('p');
        $role = $email->nextAll();
        $balance = $role->nextAll();

        $this->assertEquals('Имейл: ' . Tests\Mock\USERNAME_ADMIN, $email->text());
        $this->assertEquals('Роль: Администратор', $role->text());
        $this->assertEquals('Баланс: ' . Tests\Mock\BALANCE_ADMIN, $balance->text());

        $client->request('GET', LOCAL . '/courses/');
        $this->assertResponseOk();
        $client->clickLink('Выйти');
        $client->followRedirect();

        $this->assertResponseRedirect();
    }
}
