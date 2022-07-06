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
            $arr = $billingClient->authentication('userOne@mail.ru', 'Password');
        } catch (BillingUnavailableException $e) {
        } catch (\JsonException $e) {
        } catch (CurlException $e) {
        }
        $token = 0;
        $refresh_token = 0;
        if (array_key_exists('refresh_token', $arr)) {
            $refresh_token = $arr['refresh_token'];
        }
        try {
            $arr = $billingClient->refreshToken($refresh_token);
        } catch (BillingUnavailableException $e) {
        } catch (\JsonException $e) {
        } catch (CurlException $e) {
        }
        if (array_key_exists('token', $arr)) {
            $token = $arr['token'];
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
