<?php
namespace app\classes;

use app\models\Currency;

class Utils
{
    public static function dateBeginOfMonth($date)
    {
        $date = explode('-', $date);
        $date[2] = '01';
        return implode('-', $date);
    }

    public static function dateEndOfMonth($date)
    {
        $date = explode('-', $date);
        $date[2] = cal_days_in_month(CAL_GREGORIAN, $date[1], $date[0]);
        return implode('-', $date);
    }

    public static function dateBeginOfPreviousMonth($date)
    {
        $date = explode('-', $date);
        if (--$date[1] == 0) {
            $date[1] = 12;
            $date[0]--;
        }
        $date[1] = str_pad($date[1], 2, '0', STR_PAD_LEFT);
        $date[2] = '01';
        return implode('-', $date);
    }

    public static function dateEndOfPreviousMonth($date)
    {
        $date = explode('-', $date);
        if (--$date[1] == 0) {
            $date[1] = 12;
            $date[0]--;
        }
        $date[1] = str_pad($date[1], 2, '0', STR_PAD_LEFT);
        $date[2] = cal_days_in_month(CAL_GREGORIAN, $date[1], $date[0]);
        return implode('-', $date);
    }

    public static function bytesToMb($value,$nround = 2)
    {
        if ($nround==2) {
            $r = 100;
        } else {
            for ($r = 1, $i = 0; $i < $nround; $i++) $r *= 10;
        }
        return round($value * $r / (1024*1024)) / $r;
    }

    public static function money($value, $currency, $round = 2)
    {
        $currency = $currency ? Currency::symbol($currency) : '';

        if (is_numeric($value)) {
            $value = round($value, $round);
            $result = number_format($value, $round, '.', '');
            if ($currency) {
                $result = $result . ' ' . $currency;
            }
        } else {
            $result = $currency;
        }

        return $result;
    }

    public static function mround($value, $precision1, $precision2)
    {
        $result = $value - round($value, $precision1);
        return sprintf('%0.' . ($result == 0 ? $precision1 : $precision2) . 'f', $value);
    }

    public static function round($value, $precision, $mode = '')
    {
        $value = round($value, $precision);
        return sprintf('%0.' . $precision . 'f', ($mode === '-' ? -$value : $value));
    }

    public static function rus_fin($value, $s1, $s2, $s3) {
        if ($value == 11)
            return $s3;
        if (($value % 10) == 1)
            return $s1;
        if (($value % 100) >= 11 && ($value % 100) <= 14)
            return $s3;
        if (($value % 10) >= 2 && ($value %10) <= 4)
            return $s2;
        return $s3;
    }

}