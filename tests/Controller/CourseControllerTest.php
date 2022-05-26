<?php

namespace App\Tests\Controller;

use App\Entity\Lesson;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests;
use App\Entity\Course;

define('LOCAL', 'http://study-on.local:81');

class CourseControllerTest extends Tests\AbstractTest
{

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
        $this->assertNotFalse($form->has('course[CharacterCode]'));

        //Проверка данных в форме и данных в бд
        $this->assertEquals($nameOfCourseInRepository, $nameOfCourseInPage);
        $this->assertEquals($descriptionOfCourseInRepository, $descriptionOfCourseInPage);

        $client->request('GET', LOCAL . '/courses/' . $courseId * 404 . '/edit');

        $this->assertResponseNotFound();
    }

    //Пользователь со страницы курсов выбирает курс
    //на странице курса выбирает редактирования курса
    //В курсе меняет имя курса и описание
    //Обновляет курс
    //Переходит на страницу курса и видит изменения
    public function testUserEditCourse()
    {
        $client = self::getClient();
        $crawler = $client->request('GET', LOCAL . '/courses/');

        $this->assertResponseOk();

        $courseAll = self::getEntityManager()->getRepository(Course::class)->findAll();
        $course = $courseAll[0];
        $courseId = $course->getId();

        $href = "/courses/" . $courseId;
        $link = $crawler->filter("a[href='$href']");
        $cardBody = $link->ancestors();
        $courseNameOld = $cardBody->filter('h4[class="card-title"]')->text();
        $courseDescriptionOld = $cardBody->filter('p[class="card-text"]')->text();
//        print $courseNameOld;
//        print $courseDescriptionOld;
//        print $cardBody;

        $client->click($link->link());
        $crawler = $client->getCrawler();
//        print $crawler->html();
        $this->assertResponseOk();

        $href = "/courses/" . $courseId . "/edit";
        $link = $crawler->filter("a[href='$href']");

        $client->click($link->link());
        $crawler = $client->getCrawler();
//        print $crawler->html();
        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();
//        print $form;
        $courseNameInForm = $form->get('course[CourseName]')->getValue() . " Отредактированно";
        $courseDescriptionInForm = $form->get('course[CourseDescription]')->getValue() . " Отредактированно";

        $client->submit($form, [
            'course[CourseName]' => $courseNameInForm,
            'course[CourseDescription]' => $courseDescriptionInForm,
        ]);

        $client->followRedirect();
        $crawler = $client->getCrawler();
//        print $crawler->html();

        $this->assertResponseOk();

        $href = "/courses/" . $courseId;
        $link = $crawler->filter("a[href='$href']");
        $cardBody = $link->ancestors();
        $courseNameNew = $cardBody->filter('h4[class="card-title"]')->text();
        $courseDescriptionNew = $cardBody->filter('p[class="card-text"]')->text();

//        print $courseNameOld . " Отредактированно";
//        print $courseNameNew;

        $this->assertEquals($courseNameOld . " Отредактированно", $courseNameNew);
        $this->assertEquals($courseDescriptionOld . " Отредактированно", $courseDescriptionNew);
    }

    public function testCourseNew()
    {
        $client = self::getClient();
        $client->request('GET', LOCAL . '/courses/new');

        $this->assertResponseOk();

        $crawler = $client->request('POST', LOCAL . '/courses/new');
        $this->assertResponseOk();

        $form = $crawler->filter('form[name="course"]')->form();
        $this->assertTrue($form->has('course[CharacterCode]'));
        $this->assertTrue($form->has('course[CourseName]'));
        $this->assertTrue($form->has('course[CourseDescription]'));
    }
}
