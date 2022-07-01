<?php

namespace App\Controller;

use App\Exception\BillingUnavailableException;
use App\Form\RegistrationType;
use App\Security\BillingAuthenticator;
use App\Security\User;
use App\Service\BillingClient;
use Safe\Exceptions\CurlException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route ("/register", name="app_register")
     */
    public function register(
        Request                    $request,
        UserAuthenticatorInterface $authenticator,
        BillingAuthenticator       $formAuthenticator,
        BillingClient              $billingClient,
        AuthenticationUtils        $authenticationUtils
    ): Response {

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_profile');
        }

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        $password = $form->get('password')->getData();
        $passwordRepeat = $form->get('passwordRepeat')->getData();

        if (($password !== $passwordRepeat)) {
            $form->get('passwordRepeat')->addError(new FormError('Пароли повторяются'));
            $form->get('passwordRepeat')->isValid(false);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('email')->getData();
            $password = $form->get('password')->getData();

            try {
                $token = $billingClient->registration($username, $password);

                $userData = $billingClient->getCurrentUser($token);
            } catch (\Exception $e) {
                if ($e instanceof CustomUserMessageAuthenticationException) {
                    $error = 'Сервис временно недоступен. Попробуйте зарегистрироваться позднее';
                } else {
                    $error = $e->getMessage();
                }
                return $this->renderForm('security/register.html.twig', [
                    'form' => $form,
                    'error' => $error,
                ]);
            }

            $user->setEmail($userData['username']);
            $user->setRoles($userData['roles']);
            $user->setApiToken($token);

            return $authenticator->authenticateUser(
                $user,
                $formAuthenticator,
                $request
            );
        }


        return $this->renderForm('security/register.html.twig', [
            'form' => $form,
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
