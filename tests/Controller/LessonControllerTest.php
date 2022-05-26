<?php

namespace App\Tests\Controller;

use App\Entity\Lesson;
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

        $client = static::getClient();
        $crawler = $client->request('GET', LOCAL . '/lessons/' . $lessonId);

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
