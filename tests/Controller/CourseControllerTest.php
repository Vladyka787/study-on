<?php

namespace App\Tests\Controller;

use App\Entity\Lesson;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests;
use App\Entity\Course;

define('LOCAL', 'http://study-on.local:81');

class CourseControllerTest extends Tests\AbstractTest
{
//    public function testSomething(): void
//    {
//        $client = static::getClient();
//        $crawler = $client->request('GET', LOCAL . '/courses/');
//
//        $this->assertResponseIsSuccessful();
//        $this->assertSelectorTextContains('h1', 'Наши курсы');
//    }

    public function testToRedirect(): void
    {
        $client = static::getClient();
        $crawler = $client->request('GET', LOCAL . '/');

        $this->assertResponseRedirect();
    }

    public function testCourseIndex(): void
    {
        $client = static::getClient();
        $crawler = $client->request('GET', LOCAL . '/courses/');

        $this->assertResponseOk();
        //Проверка количества курсов
        $numberOfCoursesOnRepository = count(self::getEntityManager()->getRepository(Course::class)->findAll());
        $numberOfCoursesOnPage = $crawler->filter('div[class="card"]')->count();

        $this->assertEquals($numberOfCoursesOnRepository, $numberOfCoursesOnPage);
    }

    public function testCourseShow(): void
    {
        $courseAll = self::getEntityManager()->getRepository(Course::class)->findAll();
        $courseId = ($courseAll[0])->getId();

        $client = static::getClient();
        $crawler = $client->request('GET', LOCAL . '/courses/' . $courseId);

        $this->assertResponseOk();
        //Проверка количества уроков
        $numberOfLessonsOnRepository = count(self::getEntityManager()
            ->getRepository(Lesson::class)
            ->findLessonsByCourseId($courseId));
        $numberOfLessonsOnPage = $crawler->filter('p[class="link-lesson"]')->count();

        $this->assertEquals($numberOfLessonsOnRepository, $numberOfLessonsOnPage);

        $client->request('GET', LOCAL . '/courses/' . $courseId * 404);

        $this->assertResponseNotFound();
    }

    public function testEditCourse()
    {
        $courseAll = self::getEntityManager()->getRepository(Course::class)->findAll();
        $course = $courseAll[0];
        $courseId = $course->getId();

        $client = static::getClient();
        $crawler = $client->request('GET', LOCAL . '/courses/' . $courseId . '/edit');

        $this->assertResponseOk();

        $nameOfCourseInRepository = $course->getCourseName();
        $form = $crawler->filter('button[class="btn btn-primary"]')->form();
        $formValues = $form->getValues();
        $nameOfCourseInPage = $formValues['course[CourseName]'];
        $descriptionOfCourseInRepository = $course->getCourseDescription();
        $descriptionOfCourseInPage = $formValues['course[CourseDescription]'];

        //Проверка данных в форме и данных в бд
        $this->assertEquals($nameOfCourseInRepository, $nameOfCourseInPage);
        $this->assertEquals($nameOfCourseInRepository, $nameOfCourseInPage);
    }
}
