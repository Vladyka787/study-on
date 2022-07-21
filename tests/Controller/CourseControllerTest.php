<?php

namespace App\Tests\Controller;

use App\Entity\Lesson;
use App\Security\User;
use App\Tests\Mock\BillingClientMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests;
use App\Entity\Course;
use const App\Tests\Mock\REFRESH_TOKEN_ADMIN;
use const App\Tests\Mock\REFRESH_TOKEN_NEW_USER;
use const App\Tests\Mock\REFRESH_TOKEN_USER;
use const App\Tests\Mock\USERNAME_ADMIN;
use const App\Tests\Mock\USERNAME_NEW_USER;
use const App\Tests\Mock\USERNAME_USER;

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
        $crawler = $client->request('GET', LOCAL . '/courses/');

//        print $crawler->html();

        $newButton = $crawler->filter('body > div > a.btn-outline-secondary');
        $this->assertEquals(0, $newButton->count());

        $client->loginUser($userAdmin);
        $crawler = $client->request('GET', LOCAL . '/courses/');

        $newButton = $crawler->filter('body > div > a.btn-outline-secondary');
        $this->assertEquals(1, $newButton->count());

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
        $crawler = $client->request('GET', LOCAL . '/courses/' . $courseId);

        $editButton = $crawler->filter('body > div > div > a#edit');
        $deleteButton = $crawler->filter('body > div > div > div > form > button ');
        $addButton = $crawler->filter('body > div > div > a#add');

        $this->assertEquals(0, $editButton->count());
        $this->assertEquals(0, $deleteButton->count());
        $this->assertEquals(0, $addButton->count());

        $client->loginUser($userAdmin);
        $crawler = $client->request('GET', LOCAL . '/courses/' . $courseId);

        $editButton = $crawler->filter('body > div > div > a#edit');
        $deleteButton = $crawler->filter('body > div > div > div > form > button ');
        $addButton = $crawler->filter('body > div > div > a#add');

        $this->assertEquals(1, $editButton->count());
        $this->assertEquals(1, $deleteButton->count());
        $this->assertEquals(1, $addButton->count());

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

    public function testEditCourse(): void
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
        $crawler = $client->request('GET', LOCAL . '/courses/' . $courseId . '/edit');

        $this->assertResponseCode(403);

        $client->loginUser($userAdmin);
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
        $client = static::getClient();

        $client->disableReboot();

        static::getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock()
        );

        $service = new Tests\Service\GenerateJWTFromTests();

        $user = new User();
        $user->setEmail(USERNAME_ADMIN);
        $user->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_USER']);
        $user->setApiToken($service->generateJWT(USERNAME_ADMIN));
        $user->setApiRefreshToken(REFRESH_TOKEN_ADMIN);

        $client->loginUser($user);
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
        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();

        $courseNameInForm = $form->get('course[CourseName]')->getValue() . " Отредактированно";
        $courseDescriptionInForm = $form->get('course[CourseDescription]')->getValue() . " Отредактированно";

        $client->submit($form, [
            'course[CourseName]' => $courseNameInForm,
            'course[CourseDescription]' => $courseDescriptionInForm,
        ]);

        $client->followRedirect();
        $crawler = $client->getCrawler();

        $this->assertResponseOk();

        $href = "/courses/" . $courseId;
        $link = $crawler->filter("a[href='$href']");
        $cardBody = $link->ancestors();
        $courseNameNew = $cardBody->filter('h4[class="card-title"]')->text();
        $courseDescriptionNew = $cardBody->filter('p[class="card-text"]')->text();

        $this->assertEquals($courseNameOld . " Отредактированно", $courseNameNew);
        $this->assertEquals($courseDescriptionOld . " Отредактированно", $courseDescriptionNew);
    }

    public function testCourseNew()
    {
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
        $client->request('GET', LOCAL . '/courses/new');

        $this->assertResponseCode(403);

        $client->loginUser($userAdmin);
        $client->request('GET', LOCAL . '/courses/new');

        $this->assertResponseOk();

        $crawler = $client->request('POST', LOCAL . '/courses/new');
        $this->assertResponseOk();

        $form = $crawler->filter('form[name="course"]')->form();
        $this->assertTrue($form->has('course[CharacterCode]'));
        $this->assertTrue($form->has('course[CourseName]'));
        $this->assertTrue($form->has('course[CourseDescription]'));
    }

    public function testPrintPriceAndRentEndAndRentBeginCourse()
    {
        $client = static::GetClient();

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

        $client->loginUser($user);
        $crawler = $client->request('GET', LOCAL . '/courses/');
        $this->assertResponseOk();

        $block = $crawler->filter('body > div > div > div');

        $line = $block->filter('p');

        $str = $line->nextAll()->text();

        $chislo = preg_match("/Цена:.+\( Арендован до /u", $str);

        $this->assertEquals(1, $chislo);

        $block = $block->nextAll()->nextAll()->nextAll();

        $line = $block->filter('p');

        $str = $line->nextAll()->text();

        $chislo = preg_match("/Цена:.+\( Арендован до /u", $str);

        $this->assertEquals(0, $chislo);
    }

    public function testPresenseButtonBuy()
    {
        $client = static::GetClient();

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

        $client->loginUser($user);
        $crawler = $client->request('GET', LOCAL . '/courses/');
        $this->assertResponseOk();

        $block = $crawler->filter('body > div > div > div');

        $link = $block->nextAll()->filter('a')->link();

        $crawler = $client->click($link);

        print $crawler->html();

        $buttonBuy = $crawler->filter('body > div > div > button');

        $this->assertEquals('Купить', $buttonBuy->text());

        $client->clickLink('Техника дельфина');

        $this->assertResponseCode(403);
    }
}
