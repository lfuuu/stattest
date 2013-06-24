<?php


class citiPaymentsInfoParser
{
    function parse($f)
    {

        $f = iconv("cp1251","koi8-r//translit", $f);

        $a = explode("נעןקוהומן", $f);

        //$a = array($a[4]);
        //printdbg($a);
        unset($a[count($a)-1]);

        $D = array();
        foreach($a as $idd => $b)
        {
            $d = array();
            preg_match_all("/ףױֱֽֽ[^\d]+(?P<sum>\d+-\d+) (?P<company>.*)/", $b, $o);
            $d["sum"] = (float)str_replace("-", ".", $o["sum"][0]);
            $d["company"] = trim($o["company"][0]);
            preg_match_all("/CÞ.[^\d]+(?P<account>[\d]+)/", $b, $o);
            if($o["account"][0] == 30101810300000000202) $idx = 1; else $idx =0;

            preg_match_all("/יממ (?P<inn>[\d]+)/", $b, $o);
            $d["inn"] = trim($o["inn"][$idx]);

            preg_match_all("/CÞ.[^\d]+(?P<account>[\d]+)/", $b, $o);
            $d["account"] = $o["account"][$idx];

            preg_match_all("/נלבפוצמןו נןעץ‏ומיו[^\d]+(?P<payment_no>\d+)/", $b, $o);
            $d["payment_no"] = (int)$o["payment_no"][0];

            preg_match_all("/(\d\d\.\d\d.\d\d\d\d)\s+(\d\d\.\d\d.\d\d\d\d)\s+/", $b, $o);

            $d["date"] = $o[1][0];
            $d["oper_date"] = $o[2][0];

            $D[] = $d;
        }
        return $D;
    }
}

class citiInfo
{
    function add(&$p, &$c)
    {
        $ai = $bi = array();

        $kk = array();
        $ll = array();

        foreach($p as $aIdx => &$a)
        {
            //if ($a["A"] != "A.C40702810238090112337") continue;
            $isFound = false;
            foreach($c as $bIdx => $b)
            {
                //first loop
                //if($bIdx == 0){
                    $k = $b["sum"]."-".$b["account"];
                    if(!isset($ll[$k])) $ll[$k] = array();
                    $ll[$k][$b["payment_no"]]=$b["payment_no"];
                //}


                if($a["sum"] == $b["sum"] && $a["from"]["account"] == $b["account"])
                {
                    //find broken noref's 

                    if(strlen($a["noref"]) > 5)
                    {
                        $k = $a["sum"]."-".$b["account"];
                        if(!isset($kk[$k])){
                            $kk[$k] = array();
                        }

                        $kk[$k][] = $aIdx;
                    }

                    $ai[] = $aIdx;
                    $isFound = true;
                    $a["inn"] = $b["inn"];

                    $d = explode(".", $b["date"]);
                    $a["date"] = $d[2]."-".$d[1]."-".$d[0];

                    $d = explode(".", $b["oper_date"]);
                    $a["oper_date"] = $d[2]."-".$d[1]."-".$d[0];


                    //if($a["noref"] != $b["payment_no"]) echo "\n".$a["noref"]." => ".$b["payment_no"];
                    //$a["noref"] = $b["payment_no"];

                    if(strlen($a["noref"]) != 5){
                        $a["noref"] = $b["payment_no"];
                    }else{
                        $a["noref"] = (int)$a["noref"];
                    }

                    if(isset($a["is_set"])) print_r($a);

                    if(!isset($a["is_set"])) $a["is_set"] = 1;

                    //$e[] = $a;
                    break;
                }
            }

            if(!$isFound)
                $bi[] = $aIdx;
        }
        unset($a);

        foreach($kk as $k => $v)
        {
            if(count($v) > 1)
            {
                $lls = array_keys($ll[$k]);
                for($i = 0 ; $i < count($kk[$k]) ; $i++) {
                    $p[$v[$i]]["noref"] = $lls[$i];//$ll[$k][$i];
                }
            }
        }

        //file_put_contents("/tmp/eee", serialize($e));
    }
}
