<?php

namespace App\Controller;

use App\Exception\BillingUnavailableException;
use Safe\Exceptions\CurlException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\BillingClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    /**
     * @Route("/test", name="app_test", methods={"GET"})
     */
    public function test(BillingClient $billingClient): Response
    {
        $tokens = $billingClient->authentication('userTwo@mail.ru', 'SuperPassword');

        $token = $tokens['token'];
        $characterCode = 'kursy_po_strizhke';
        $array = [
            'type' => 'rent',
            'title' => 'Курс со StudyOn',
            'code' => 'new_course',
            'price' => 500.99,
        ];

        try {
            $data = $billingClient->editCourse($token, $characterCode, $array);
        } catch (BillingUnavailableException $e) {
        } catch (\JsonException $e) {
        } catch (CurlException $e) {
        }

        return new Response();
    }
}
