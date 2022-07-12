<?php

namespace App\Controller;

use App\Exception\BillingUnavailableException;
use App\Repository\CourseRepository;
use App\Service\BillingClient;
use Safe\Exceptions\CurlException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Security\User;

class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="app_profile")
     */
    public function profile(BillingClient $billingClient, Security $security)
    {
        $user = $security->getUser();
        try {
            $dataUser = $billingClient->getCurrentUser($user->getApiToken());
        } catch (BillingUnavailableException|\JsonException|CurlException $e) {
        }
        return $this->render('profile/index.html.twig', [
            'balance' => $dataUser['balance'],
        ]);
    }

    /**
     * @Route("/profile/history", name="app_profile_history")
     */
    public function transactionHistory(
        BillingClient $billingClient,
        Security $security,
        CourseRepository $courseRepository
    ) {
        $courses = $courseRepository->findAll();
        $user = $security->getUser();
        $token = $user->getApiToken();

        $transactions = $billingClient->getTransactions($token);

        return $this->render('profile/history.html.twig', [
            'courses' => $courses,
            'transactions' => $transactions
        ]);
    }
}
