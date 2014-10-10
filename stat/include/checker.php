<?php

/**
 * проверяет на корректность введенных данных
 */
class checker 
{
    public function isEmpty($value, $error)
    {
        if (empty($value)) {
            throw new Exception($error);
        }
    }

    public function isUsed($value, $field, $table, $id, $error)
    {
        global $db;

        if (
                count($db->AllRecords(
                    "select id from `".$table."` " .
                    "where `".$field."` = '".mysql_escape_string($value)."' " .
                    "and id != '".$id."'"
                    )) > 0
           ) {
            throw new Exception($error);
        }
    }

    public function isDigits($value, $error)
    {
        if (preg_match_all("/^[0-9]+$/", $value, $o))
        {
            return;
        }
        throw new Exception($error);
    }

    public function isZero($value, $error)
    {
        if ($value == 0)
        {
            throw new Exception($error);
        }
    }

    public function isValideIp(&$ip, $error)
    {
        $ip = trim($ip);
        if (!preg_match_all("/^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$/", $ip, $out)) {
            throw new Exception($error);
        }

        if(
                !(
                    0 <= $out[1][0] && $out[1][0] <= 255 &&
                    0 <= $out[2][0] && $out[2][0] <= 255 &&
                    0 <= $out[3][0] && $out[3][0] <= 255 &&
                    0 <= $out[4][0] && $out[4][0] <= 255
                 )
          ){
            throw new Exception($error);
        }
    }

    public function number_isBetween($num, $valFrom, $valTo, $errorMsg)
    {
        $num = (float)$num;
        if ($num < $valFrom || $num > $valTo)
        {
            throw new Exception($errorMsg);
        }
    }

    public function isAlnum($value, $errorStr)
    {
        if(!preg_match_all("/^[0-9a-zA-Z]+$/", $value, $o))
        {
            throw new Exception($errorStr);
        }
    }
}
