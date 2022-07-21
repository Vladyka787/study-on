<?php

namespace App\Tests\Controller;

use App\Entity\Lesson;
use App\Security\User;
use App\Tests\Mock\BillingClientMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests;
use App\Entity\Course;
use const App\Tests\Mock\REFRESH_TOKEN_ADMIN;
use const App\Tests\Mock\REFRESH_TOKEN_USER;
use const App\Tests\Mock\USERNAME_ADMIN;
use const App\Tests\Mock\USERNAME_USER;

define('LOCAL', 'http://study-on.local:81');

class LessonControllerTest extends Tests\AbstractTest
{
    public function testLessonShow()
    {
        $lessonAll = self::getEntityManager()->getRepository(Lesson::class)->findAll();
        $lesson = $lessonAll[0];
        $lessonId = $lesson->getId();

        $client = static::getClient();

        $client->disableReboot();

        static::getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock()
        );

        $service = new Tests\Service\GenerateJWTFromTests();

        $user = new User();
        $user->setEmail(USERNAME_USER);
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken($service->generateJWT(USERNAME_USER));
        $user->setApiRefreshToken(REFRESH_TOKEN_USER);

        $userAdmin = new User();
        $userAdmin->setEmail(USERNAME_ADMIN);
        $userAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);
        $userAdmin->setApiToken($service->generateJWT(USERNAME_ADMIN));
        $userAdmin->setApiRefreshToken(REFRESH_TOKEN_ADMIN);

        $crawler = $client->request('GET', LOCAL . '/lessons/' . $lessonId);
        $this->assertResponseRedirect();

        $client->loginUser($user);
        $crawler = $client->request('GET', LOCAL . '/lessons/' . $lessonId);

        $editButton = $crawler->filter('body > div > div > a ');
        $deleteButton=$crawler->filter('body > div > div > form > button');

        $this->assertEquals(0, $editButton->count());
        $this->assertEquals(0, $deleteButton->count());

        $client->loginUser($userAdmin);
        $crawler = $client->request('GET', LOCAL . '/lessons/' . $lessonId);

//        var_dump($client->getResponse()->getContent());
        $editButton=$crawler->filter('body > div > div > a ');
        $deleteButton=$crawler->filter('body > div > div > form > button');

        $this->assertEquals(1, $editButton->count());
        $this->assertEquals(1, $deleteButton->count());

        $this->assertResponseOk();

        $client->request('GET', LOCAL . '/lessons/' . $lessonId * 404);
        $this->assertResponseNotFound();
    }

    public function testLessonEdit()
    {
        $lessonAll = self::getEntityManager()->getRepository(Lesson::class)->findAll();
        $lesson = $lessonAll[0];
        $lessonId = $lesson->getId();

        $client = static::getClient();

        $client->disableReboot();

        static::getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock()
        );

        $service = new Tests\Service\GenerateJWTFromTests();

        $user = new User();
        $user->setEmail(USERNAME_USER);
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken($service->generateJWT(USERNAME_USER));
        $user->setApiRefreshToken(REFRESH_TOKEN_USER);

        $userAdmin = new User();
        $userAdmin->setEmail(USERNAME_ADMIN);
        $userAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);
        $userAdmin->setApiToken($service->generateJWT(USERNAME_ADMIN));
        $userAdmin->setApiRefreshToken(REFRESH_TOKEN_ADMIN);

        $client->loginUser($user);
        $crawler = $client->request('GET', LOCAL . '/lessons/' . $lessonId . '/edit');

        $this->assertResponseCode(403);

        $client->loginUser($userAdmin);
        $crawler = $client->request('GET', LOCAL . '/lessons/' . $lessonId . '/edit');

        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();
        $this->assertTrue($form->has('lesson[LessonName]'));
        $this->assertTrue($form->has('lesson[LessonContent]'));
        $this->assertTrue($form->has('lesson[LessonNumber]'));
        $this->assertTrue($form->has('lesson[Course]'));
        $this->assertEquals($form->get('lesson[LessonName]')->getValue(), $lesson->getLessonName());
        $this->assertEquals($form->get('lesson[LessonContent]')->getValue(), $lesson->getLessonContent());
        $this->assertEquals($form->get('lesson[LessonNumber]')->getValue(), $lesson->getLessonNumber());

        $client->request('GET', LOCAL . '/lessons/' . $lessonId * 404 . '/edit');

        $this->assertResponseNotFound();
    }

    public function testLessonNew()
    {
        $courseAll = self::getEntityManager()->getRepository(Course::class)->findAll();
        $course = $courseAll[0];
        $courseId = $course->getId();

        $client = static::getClient();

        $client->disableReboot();

        static::getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock()
        );

        $service = new Tests\Service\GenerateJWTFromTests();

        $user = new User();
        $user->setEmail(USERNAME_USER);
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken($service->generateJWT(USERNAME_USER));
        $user->setApiRefreshToken(REFRESH_TOKEN_USER);

        $userAdmin = new User();
        $userAdmin->setEmail(USERNAME_ADMIN);
        $userAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);
        $userAdmin->setApiToken($service->generateJWT(USERNAME_ADMIN));
        $userAdmin->setApiRefreshToken(REFRESH_TOKEN_ADMIN);

        $client->loginUser($user);
        $crawler = $client->request('GET', LOCAL . '/lessons/new?course_id=' . $courseId);

        $this->assertResponseCode(403);

        $client->loginUser($userAdmin);
        $crawler = $client->request('GET', LOCAL . '/lessons/new?course_id=' . $courseId);

        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();
//        print $form;
        $this->assertTrue($form->has('lesson[LessonName]'));
        $this->assertTrue($form->has('lesson[LessonContent]'));
        $this->assertTrue($form->has('lesson[LessonNumber]'));
        $this->assertTrue($form->has('lesson[Course]'));
        $this->assertEquals($form->get('lesson[Course]')->getValue(), $courseId);

        $client->request('POST', LOCAL . '/lessons/new?course_id=' . $courseId);
        $this->assertResponseOk();

        $client->request('GET', LOCAL . '/lessons/new?course_id=' . $courseId * 500);
        $this->assertResponseCode('500');

        $form->get('lesson[LessonName]')->setValue('text');
        $form->get('lesson[LessonContent]')->setValue('text');
        $form->get('lesson[LessonNumber]')->setValue(99999);

        $client->submit($form);
        $crawler = $client->getCrawler();
        $this->assertSelectorExists('div[class="invalid-feedback d-block"]');
//        $error = $crawler->filter('div[class="invalid-feedback d-block"]')->text();
//        print $error;
    }
}
