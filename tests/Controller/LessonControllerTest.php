<?php

namespace App\Tests\Controller;

use App\Entity\Lesson;
use App\Security\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests;
use App\Entity\Course;

define('LOCAL', 'http://study-on.local:81');

class LessonControllerTest extends Tests\AbstractTest
{
    public function testLessonShow()
    {
        $lessonAll = self::getEntityManager()->getRepository(Lesson::class)->findAll();
        $lesson = $lessonAll[0];
        $lessonId = $lesson->getId();

        $user = new User();
        $user->setEmail('userTwo@mail.ru');
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken('token');

        $userAdmin = new User();
        $userAdmin->setEmail('userTwo@mail.ru');
        $userAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);
        $userAdmin->setApiToken('token');

        $client = self::getClient();
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

        $user = new User();
        $user->setEmail('userTwo@mail.ru');
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken('token');

        $userAdmin = new User();
        $userAdmin->setEmail('userTwo@mail.ru');
        $userAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);
        $userAdmin->setApiToken('token');

        $client = self::getClient();

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

        $user = new User();
        $user->setEmail('userTwo@mail.ru');
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken('token');

        $userAdmin = new User();
        $userAdmin->setEmail('userTwo@mail.ru');
        $userAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);
        $userAdmin->setApiToken('token');

        $client = self::getClient();

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
