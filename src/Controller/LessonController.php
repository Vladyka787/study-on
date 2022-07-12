<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Service\BillingClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/lessons")
 */
class LessonController extends AbstractController
{
    /**
     * @Route("/", name="app_lesson_index", methods={"GET"})
     */
    public function index(LessonRepository $lessonRepository): Response
    {
        return $this->render('lesson/index.html.twig', [
            'lessons' => $lessonRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_lesson_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function new(Request $request, LessonRepository $lessonRepository, CourseRepository $courseRepository): Response
    {
        $lesson = new Lesson();
        $courseId = (int)$request->query->get('course_id');
        $lesson->setCourse($courseRepository->find($courseId));
        $course = $lesson->getCourse();
        $form = $this->createForm(
            LessonType::class,
            $lesson,
            ['course_id' => $courseId]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->add($lesson);
            return $this->redirectToRoute('app_course_show', ['id' => $lesson->getCourse()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'course' => $course,
        ]);
    }

    /**
     * @Route("/{id}", name="app_lesson_show", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function show(Lesson $lesson, Security $security, BillingClient $billingClient): Response
    {
        $course = $lesson->getCourse();

        $user = $security->getUser();
        $token = $user->getApiToken();

        $filter = [];
        $filter['type'] = 'payment';
        $filter['course_code'] = $course->getCharacterCode();
        $filter['skip_expired'] = true;

        $result = $billingClient->getTransactions($token, $filter);

        $courseData = $billingClient->getConcreteCourse($course->getCharacterCode());

        if (($result === []) && ($courseData['type'] !== 'free')) {
            throw new AccessDeniedException('Отказано в доступе');
        }

        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
            'course' => $course,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_lesson_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function edit(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);
        $course = $lesson->getCourse();

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->add($lesson);
            return $this->redirectToRoute('app_course_show', ['id' => $lesson->getCourse()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('lesson/edit.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'course' => $course,
        ]);
    }

    /**
     * @Route("/{id}", name="app_lesson_delete", methods={"POST"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function delete(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $lesson->getId(), $request->request->get('_token'))) {
            $lessonRepository->remove($lesson);
        }

        return $this->redirectToRoute('app_course_show', ['id' => $lesson->getCourse()->getId()], Response::HTTP_SEE_OTHER);
    }
}
