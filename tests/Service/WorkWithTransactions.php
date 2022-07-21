<?php

namespace App\Tests\Service;

use const App\Tests\Mock\CHARACTER_CODE_BEG;
use const App\Tests\Mock\CHARACTER_CODE_STRIZHKA;

class WorkWithTransactions
{
    public function getAllTransactionByUser($transactions, array $filter = null)
    {
        $result = [];
        foreach ($transactions as $transaction) {
            $arr = [];
            if ($this->workFilter($filter, $transaction)) {
                $arr['id'] = $transaction['id'];
                $arr['created_at'] = $transaction['created_at'];
                $arr['type'] = $transaction['type'];
                if ($arr['type'] === 'payment') {
                    $arr['course_code'] = $transaction['course_code'];
                }
                $arr['amount'] = $transaction['amount'];
                $result[] = $arr;
            }
        }

        return $result;
    }

    private function workFilter($filter = null, $transaction)
    {
        if ($filter !== null) {
            if (array_key_exists('type', $filter)) {
                if ($filter['type'] === $transaction['type']) {
                } else {
                    return false;
                }
            }
            if (array_key_exists('course_code', $filter)) {
                if (array_key_exists('course_code', $transaction)) {
                    if ($filter['course_code'] === $transaction['course_code']) {
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            if (array_key_exists('skip_expired', $filter)) {
                if ($filter['skip_expired'] == "true") {
                    if (($transaction['course_code'] == CHARACTER_CODE_STRIZHKA) || ($transaction['course_code'] == CHARACTER_CODE_BEG)) {
                        $now = strtotime('now');
                        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $transaction['created_at']);

                        $then = strtotime($date->modify('+7 day')->format('Y-m-d H:i:sP'));
                        if ($now < $then) {

                        } else {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function updateCreateDate($transactions)
    {
        $result = [];
        foreach ($transactions as $transaction) {
            $date = new \DateTime("now", new \DateTimeZone('UTC'));
            $transaction['created_at'] = $date->format('Y-m-d\TH:i:sP');

//          Сделано для теста аренды. Одна аренда только, только закончилась, другая только началась.
//          Проверить наличие одной и отсутсвие другой.
            if ($transaction['id'] == 4) {
                $transaction['created_at'] = $date->modify('-1 week -1 sec')->format('Y-m-d\TH:i:sP');
            }
            $result[] = $transaction;
        }

        return $result;
    }
}