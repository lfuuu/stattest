<?php

class checkFormat
{
    public function isEmpty($value, $errorStr)
    {
        if(empty($value))
            throw new Exception($errorStr);
    }

    public function isNotInt($value, $errorStr)
    {
        if(!is_numeric($value) || intval($value) != $value)
            throw new Exception($errorStr);
    }

    public function isZero($value, $errorStr)
    {
        if(!is_numeric($value) || intval($value) == 0)
            throw new Exception($errorStr);
    }

    public function isEq($value1, $value2, $errorStr)
    {
        if($value1 == $value2)
            throw new Exception($errorStr);
    }

    public function isDateDB($value, $errorStr)
    {
        self::NotTestRegexp($value, "\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}", $errorStr);
    }

    public function isDate($value, $errorStr)
    {
        self::notTestRegexp($value, "\d{4}-\d{2}-\d{2}", $errorStr);
    }

    public function isTime($value, $errorStr)
    {
        self::notTestRegexp($value, "\d{2}:\d{2}", $errorStr);
    }

    public function isTimeFull($value, $errorStr)
    {
        self::notTestRegexp($value, "\d{2}:\d{2}:\d{2}", $errorStr);
    }

    public function isInArray($value, $values, $errorStr)
    {
        if(in_array($value, $values))
            throw new Exception($errorStr);
    }

    public function isNotBetween($value, $from, $to, $errorStr)
    {
        if($from > $value || $value > $to)
            throw new Exception($errorStr);
    }

    public function isNotInArray($value, $values, $errorStr)
    {
        if(!in_array($value, $values))
            throw new Exception($errorStr);
    }


    public function notTestRegexp($value, $regexp, $errorStr)
    {
        if(!preg_match("#^".$regexp."$#", $value))
            throw new Exception($errorStr);
    }
}

