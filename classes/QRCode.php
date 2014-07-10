<?php

class QRCode
{
    public static $codes = array(
            "bill"  => array("code" => "01", "c" => "bill",          "name" => "óÞÅÔ"),
            "akt-1" => array("code" => "11", "c" => "akt", "s" => 1, "name" => "áËÔ 1"),
            "akt-2" => array("code" => "12", "c" => "akt", "s" => 2, "name" => "áËÔ 2"),
            "upd-1" => array("code" => "21", "c" => "upd", "s" => 1, "name" => "õðä 1"),
            "upd-2" => array("code" => "22", "c" => "upd", "s" => 2, "name" => "õðä 2"),
            "upd-3" => array("code" => "23", "c" => "upd", "s" => 3, "name" => "õðä ô"),
            );

    public function encode($docType, $billNo)
    {
        self::_prepareBillNo($billNo);

        if(!isset(self::$codes[$docType])) return false;

        return self::$codes[$docType]["code"].$billNo;
    }

    private function _prepareBillNo(&$billNo)
    {
        $billNo = str_replace("-", "1", $billNo);
        $billNo = str_replace("/", "2", $billNo);
    }

    public function getNo($billNo)
    {
        $billNo = str_replace("-", "1", $billNo);
        $billNo = str_replace("/", "2", $billNo);

        foreach(self::$codes as $c)
        {
            if(isset($c["s"]))
            {
                $r[$c["c"]][$c["s"]] = $c["code"]."".$billNo;
            }else{
                $r[$c["c"]] = $c["code"]."".$billNo;
            }
        }

        return $r;
    }

    public function decodeNo($no)
    {
        if(strlen($no) == 13)
        {
            $type = self::_getType(substr($no, 0, 2));
            $number = self::_getNumber(substr($no, 2));

            if($type)
            {
                return array("type" => $type, "number" => $number);
            }
        }
        return false;
    }

    private function _getType($t)
    {
        foreach(self::$codes as $c)
        {
            if($c["code"] == $t) return $c;
        }

        return false;
    }

    private function _getNumber($no)
    {
        switch($no[6])
        {
            case '1' : $no[6] = "-"; break;
            case '2' : $no[6] = "/"; break;
            default: return false;
        }

        return $no;
    }

    public function decodeFile($file)
    {
        exec("zbarimg -q ".$file, $o);

        if(!$o) 
            return false;

        foreach($o as $l)
        {
            list($code, $number) = explode(":", $l);

            if($code == "QR-Code")
                return $number;
        }

        return false;
    }
}
