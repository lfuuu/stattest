<?php
namespace app\classes;

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
}