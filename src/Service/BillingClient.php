<?php

namespace App\Service;

use App\Exception\BillingUnavailableException;
use JsonException;
use Safe\Exceptions\CurlException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

const URL_REG = "billing.study-on.local/api/v1/register";
const URL_AUTH = "billing.study-on.local/api/v1/auth";
const URL_GET_USER = "billing.study-on.local/api/v1/users/current";
const URL_REFRESH = "billing.study-on.local/api/v1/token/refresh";
const URL_COURSES = "billing.study-on.local/api/v1/courses";
const URL_TRANSACTIONS = "billing.study-on.local/api/v1/transactions";

//Глянуть и может убрать из composer.json
//"ext-curl": "*",
//"ext-json": "*"
class BillingClient
{
    /**
     * @param string $username
     * @param string $password
     * @return mixed
     * @throws BillingUnavailableException
     * @throws CurlException
     * @throws JsonException
     */
    public function registration(string $username, string $password)
    {
        $cURL_descriptor = curl_init(URL_REG);

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        $parameter = [];
        $parameter[] = 'Content-Type: application/json';

        $json = [];
        $json['username'] = $username;
        $json['password'] = $password;

        $jsonString = json_encode($json, JSON_THROW_ON_ERROR);

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_HTTPHEADER => $parameter,
            CURLOPT_POSTFIELDS => $jsonString,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);


        if (array_key_exists('errors', $data)) {
            throw new BillingUnavailableException('Некорректные данные');
        }

        if (array_key_exists('errorRepeat', $data)) {
            throw new BillingUnavailableException('Данный имейл уже зарегистрирован');
        }

        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }


        return $data;
    }

    /**
     * @param string $username
     * @param string $password
     * @return mixed
     * @throws JsonException|BillingUnavailableException
     * @throws CurlException
     */
    public function authentication(string $username, string $password)
    {
        $cURL_descriptor = curl_init(URL_AUTH);

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        $parameter = [];
        $parameter[] = 'Content-Type: application/json';

        $json = [];
        $json['username'] = $username;
        $json['password'] = $password;

        $jsonString = json_encode($json, JSON_THROW_ON_ERROR);

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_HTTPHEADER => $parameter,
            CURLOPT_POSTFIELDS => $jsonString,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);

        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }

        return $data;
    }

    /**
     * @param string $token
     * @return mixed
     * @throws BillingUnavailableException|JsonException
     * @throws CurlException
     */
    public function getCurrentUser(string $token)
    {
        $cURL_descriptor = curl_init(URL_GET_USER);

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        $parameter = [];
        $parameter[] = 'Authorization: Bearer ' . $token;

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_HTTPHEADER => $parameter,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);

        if ($responseCode === 401) {
            throw new CustomUserMessageAuthenticationException('Токен JWT с истекшим сроком действия');
        }
        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }

        return $data;
    }


    public function refreshToken(string $refreshToken)
    {
        $cURL_descriptor = curl_init(URL_REFRESH);

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        $array['refresh_token'] = $refreshToken;

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_POSTFIELDS => $array,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);

        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }

        return $data;
    }

    public function getDataAllCourses()
    {
        $cURL_descriptor = curl_init(URL_COURSES);

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_RETURNTRANSFER => true,
        ]);


        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);

        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }

        return $data;
    }

    public function getConcreteCourse(string $characterCode)
    {
        $cURL_descriptor = curl_init(URL_COURSES . '/' . $characterCode);

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);

        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }

        return $data;
    }

    public function payForTheCourse(string $characterCode, string $token)
    {
        $cURL_descriptor = curl_init(URL_COURSES . '/' . $characterCode . '/pay');

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        $parameter = [];
        $parameter[] = 'Content-Type: application/json';
        $parameter[] = 'Authorization: Bearer ' . $token;

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_HTTPHEADER => $parameter,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
        ]);

        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);

        if ($responseCode === 406) {
            return $data;
        }
        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }

        return $data;
    }

    public function getTransactions(string $token, array $filter = null)
    {
        $wrapper = [];
        $wrapper['filter'] = $filter;
        $arr = http_build_query($wrapper);
        $cURL_descriptor = curl_init(URL_TRANSACTIONS . "?" . $arr);

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        $parameter = [];
        $parameter[] = 'Content-Type: application/json';
        $parameter[] = 'Authorization: Bearer ' . $token;

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_HTTPHEADER => $parameter,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);

        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }

        return $data;
    }

    public function editCourse(string $token, string $characterCode, array $dataEditCourse)
    {
        $cURL_descriptor = curl_init(URL_COURSES . '/' . $characterCode);

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        $parameter = [];
        $parameter[] = 'Content-Type: application/json';
        $parameter[] = 'Authorization: Bearer ' . $token;

        $json = $dataEditCourse;

        $jsonString = json_encode($json, JSON_THROW_ON_ERROR);

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_HTTPHEADER => $parameter,
            CURLOPT_POSTFIELDS => $jsonString,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);

        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }

        return $data;
    }

    public function createCourse(string $token, array $dataNewCourse)
    {
        $cURL_descriptor = curl_init(URL_COURSES);

        if ($cURL_descriptor === false) {
            throw new CurlException();
        }

        $parameter = [];
        $parameter[] = 'Content-Type: application/json';
        $parameter[] = 'Authorization: Bearer ' . $token;

        $json = $dataNewCourse;

        $jsonString = json_encode($json, JSON_THROW_ON_ERROR);

        curl_setopt_array($cURL_descriptor, [
            CURLOPT_HTTPHEADER => $parameter,
            CURLOPT_POSTFIELDS => $jsonString,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $dataJson = curl_exec($cURL_descriptor);
        $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);

        $responseCode = curl_getinfo($cURL_descriptor, CURLINFO_RESPONSE_CODE);

        curl_close($cURL_descriptor);

        if ($responseCode >= 400) {
            throw new BillingUnavailableException();
        }

        return $data;
    }
}
