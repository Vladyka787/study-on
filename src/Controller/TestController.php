<?php

namespace App\Controller;

use App\Exception\BillingUnavailableException;
use Safe\Exceptions\CurlException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\BillingClient;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    /**
     * @Route("/test", name="app_test", methods={"GET"})
     */
    public function test(BillingClient $billingClient)
    {
        try {
            $token = $billingClient->registration('22@mail.ru', '22password');
        } catch (BillingUnavailableException $e) {
        } catch (\JsonException $e) {
        } catch (CurlException $e) {
        }
        try {
            $billingClient->getCurrentUser($token);
        } catch (BillingUnavailableException $e) {
        } catch (\JsonException $e) {
        } catch (CurlException $e) {
        }

        return 0;
    }
}
