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
        try {
            $arr = $billingClient->authentication('userOne@mail.ru', 'Password');
        } catch (BillingUnavailableException $e) {
        } catch (\JsonException $e) {
        } catch (CurlException $e) {
        }
//        $token = 0;
//        $refresh_token = 0;
//        if (array_key_exists('refresh_token', $arr)) {
//            $refresh_token = $arr['refresh_token'];
//        }
//        try {
//            $arr = $billingClient->refreshToken($refresh_token);
//        } catch (BillingUnavailableException $e) {
//        } catch (\JsonException $e) {
//        } catch (CurlException $e) {
//        }
//        if (array_key_exists('token', $arr)) {
//            $token = $arr['token'];
//        }
//        try {
//            $billingClient->getCurrentUser($token);
//        } catch (BillingUnavailableException $e) {
//        } catch (\JsonException $e) {
//        } catch (CurlException $e) {
//        }

        $str = 'kursy_po_strizhke';

        $filter = [];
        $filter['type'] = 'payment';
        $filter['course_code'] = 'kursy_po_strizhke';
        $filter['skip_expired'] = true;

        try {
            $data = $billingClient->getTransactions($arr['token'], $filter);
        } catch (BillingUnavailableException|\JsonException|CurlException $e) {
        }

        return new Response();
    }
}
