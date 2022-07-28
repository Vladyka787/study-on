<?php

namespace App\Controller;

use App\Entity\Course;
use App\Exception\BillingUnavailableException;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Service\BillingClient;
use Safe\Exceptions\CurlException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/courses")
 */
class CourseController extends AbstractController
{
    /**
     * @Route("/", name="app_course_index", methods={"GET"})
     */
    public function index(
        CourseRepository $courseRepository,
        BillingClient    $billingClient,
        Security         $security
    ): Response
    {
        $dataCourses = $billingClient->getDataAllCourses();

        $filter = [];
        $filter['type'] = 'payment';
        $filter['skip_expired'] = true;

        $user = $security->getUser();
        $transaction = [];
        if ($user !== null) {
            $token = $user->getApiToken();
            $dataTransactions = $billingClient->getTransactions($token, $filter);


            foreach ($dataTransactions as $dataTransaction) {
                foreach ($dataCourses as $dataCourse) {
                    if ($dataTransaction['course_code'] === $dataCourse['code']) {
                        if ($dataCourse['type'] === 'rent') {
                            $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $dataTransaction['created_at']);
                            $dataTransaction['expires_at'] = $date->modify('+7 day')->format('Y-m-d');
                        }
                        $transaction[] = $dataTransaction;
                    }
                }
            }
        }

        try {
            return $this->render('course/index.html.twig', [
                'courses' => $courseRepository->findAll(),
                'dataCourses' => $dataCourses,
                'dataTransactions' => $transaction,
            ]);
        } catch (BillingUnavailableException|CurlException|\JsonException $e) {
        }
    }

    /**
     * @Route("/new", name="app_course_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function new(
        Request          $request,
        CourseRepository $courseRepository,
        BillingClient    $billingClient,
        Security         $security
    ): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        $checkForSpace = $form->get('CharacterCode')->getData();

        for ($i = 0, $iMax = strlen($checkForSpace); $i < $iMax; $i++) {
            if ($checkForSpace[$i] == ' ') {
                $form->get('CharacterCode')->addError(new FormError('Буквенный код не должен содержать пробелов.'));
                $form->get('CharacterCode')->isValid(false);
                break;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            $token = $user->getApiToken();

            $type = $form->get('Type')->getData();
            $title = $form->get('CourseName')->getData();
            $code = $form->get('CharacterCode')->getData();
            $price = $form->get('Price')->getData();

            $dataCreateCourse = [];

            $dataCreateCourse ['title'] = $title;
            $dataCreateCourse ['code'] = $code;
            $dataCreateCourse ['price'] = $price;
            if ($price == 0) {
                $dataCreateCourse['type'] = 'free';
                $dataCreateCourse ['price'] = null;
            } elseif ($type == 'full') {
                $dataCreateCourse ['type'] = 'buy';
            } elseif ($type == 'rent') {
                $dataCreateCourse ['type'] = 'rent';
            }

            $exam = $courseRepository->findBy(['CharacterCode' => $code]);

            if ($exam == []) {
                try {
                    $result = $billingClient->createCourse($token, $dataCreateCourse);
                } catch (BillingUnavailableException $e) {
                } catch (\JsonException $e) {
                } catch (CurlException $e) {
                }

                if ($result['success']) {
                    $course->setCharacterCode($code);
                    $courseRepository->add($course);
                    return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
                }
            }
            else
            {
                $form->get('CharacterCode')->addError(new FormError('Код курса должен быть уникальным.'));
                $form->get('CharacterCode')->isValid(false);
            }
        }

        return $this->renderForm('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_course_show", methods={"GET"})
     */
    public function show(
        Course           $course,
        LessonRepository $lessonRepository,
        BillingClient    $billingClient,
        Security         $security
    ): Response
    {
        $lessons = $lessonRepository->findLessonsByCourseId($course->getId());

        $dataCourse = $billingClient->getConcreteCourse($course->getCharacterCode());

        $filter = [];
        $filter['course_code'] = $dataCourse['code'];
        $filter['skip_expired'] = true;

        $user = $security->getUser();
        $dataTransactions = [];
        $canBuy = null;

        if ($user !== null) {
            $token = $user->getApiToken();
            $dataTransactions = $billingClient->getTransactions($token, $filter);

            $currentUserData = $billingClient->getCurrentUser($token);

            if (array_key_exists('price', $dataCourse)) {
                $balance = $currentUserData['balance'];

                if ($dataCourse['price'] < $balance) {
                    $canBuy = true;
                } else {
                    $canBuy = false;
                }
            }
        }


        return $this->render('course/show.html.twig', [
            'course' => $course,
            'lessons' => $lessons,
            'dataCourse' => $dataCourse,
            'dataTransaction' => $dataTransactions,
            'canBuy' => $canBuy
        ]);
    }

    /**
     * @Route("/{id}/buy", name="app_course_buy", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function buy(
        Course        $course,
        BillingClient $billingClient,
        Security      $security
    ): Response
    {
        $user = $security->getUser();
        $token = $user->getApiToken();
        $result = $billingClient->payForTheCourse($course->getCharacterCode(), $token);

        if (array_key_exists('success', $result)) {
            $this->addFlash('success', 'Курс успешно оплачен');
        }
        if (array_key_exists('code', $result)) {
            $this->addFlash('error', 'Ошибка: ' . $result['message']);
        }

        return $this->redirectToRoute('app_course_show', ['id' => $course->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/edit", name="app_course_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function edit(
        Request          $request,
        Course           $course,
        CourseRepository $courseRepository,
        BillingClient    $billingClient,
        Security         $security
    ): Response
    {
        $data = $billingClient->getConcreteCourse($course->getCharacterCode());

        $oldCharacterCode = $course->getCharacterCode();

        $type = $data['type'];
        $price = null;
        if (array_key_exists('price', $data)) {
            $price = $data['price'];
        }
        $form = $this->createForm(CourseType::class, $course, ['type' => $type, 'price' => $price]);
        $form->handleRequest($request);

        $checkForSpace = $form->get('CharacterCode')->getData();

        for ($i = 0, $iMax = strlen($checkForSpace); $i < $iMax; $i++) {
            if ($checkForSpace[$i] == ' ') {
                $form->get('CharacterCode')->addError(new FormError('Буквенный код не должен содержать пробелов.'));
                $form->get('CharacterCode')->isValid(false);
                break;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            $token = $user->getApiToken();

            $type = $form->get('Type')->getData();
            $title = $form->get('CourseName')->getData();
            $code = $form->get('CharacterCode')->getData();
            $price = $form->get('Price')->getData();

            $dataEditCourse = [];

            $dataEditCourse ['title'] = $title;
            $dataEditCourse ['code'] = $code;
            $dataEditCourse ['price'] = $price;
            if ($price == 0) {
                $dataEditCourse['type'] = 'free';
                $dataEditCourse ['price'] = null;
            } elseif ($type == 'full') {
                $dataEditCourse ['type'] = 'buy';
            } elseif ($type == 'rent') {
                $dataEditCourse ['type'] = 'rent';
            }

            $exam = $courseRepository->findBy(['CharacterCode' => $code]);

            if (($exam == []) || ($oldCharacterCode == $code)) {
                try {
                    $result = $billingClient->editCourse($token, $oldCharacterCode, $dataEditCourse);
                } catch (BillingUnavailableException $e) {
                } catch (\JsonException $e) {
                } catch (CurlException $e) {
                }

                if ($result['success']) {
                    $course->setCharacterCode($code);
                    $courseRepository->add($course);
                    return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
                }
            }
        }

        return $this->renderForm('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_course_delete", methods={"POST"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function delete(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $courseRepository->remove($course);
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }
}
