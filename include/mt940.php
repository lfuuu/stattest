<?php

if(!defined("PAYMENTS_FILES_PATH")) define("PAYMENTS_FILES_PATH", "store/payments/");

class banks{

    static private $names = array(
            "301422002" => array("citi_mcn", "citi"),
            "301423001" => array("citi_all4net_usd", "citi"),
            "301423002" => array("citi_all4net_rub", "citi"),
            "40702810500540000002" => array("ural_all4net", "ural"),
            "40702810800540001507" => array("ural_cmc", "ural"),
            "40702810700320000882" => array("mos_mcn", "mos"),
            "40702810038110015462" => array("sber_telekom", "sber")
            );

    function detect($h)
    {
        $h = str_replace(array("\n", "\r") , "", $h);
        $pa = array();
        foreach(self::$names as $p => $v)
        {
            if(($pos = strpos($h, (string)$p)) !== false)
            {
               $pa[$pos] = $p;
            }
        }

        if($pa)
        {
            $v =self::$names[$pa[min(array_keys($pa))]];
            return array("file" => $v[0], "bank" => $v[1]);
        }

        return false;
    }

    function getFileName($p){
        return self::$names[$p][0];
    }

    function getBank($p){
        return self::$names[$p][1];
    }
}

class mt940_list_manager
{
    function parseAndSave($c)
    {
        $lists = explode("\r\n-\r\n", $c);
        if(count($lists) > 1)
            unset($lists[count($lists)-1]);


        foreach($lists as $c)
        {
            $m = new mt940($c);
            $p = $m->getPays();
            $payAcc = $m->getPayAcc();

            $fName = banks::getFileName($payAcc);

            if($p)
            {
                $d = $p[0]["date"];

                $fName = PAYMENTS_FILES_PATH.$fName."__".date("d-m-Y", strtotime($d)).".txt";
                $pFile = fopen($fName, "wb");
                fwrite($pFile, iconv("cp1251", "koi8-r//TRANSLIT", $c));
                fclose($pFile);
                exec("chmod a+w ".$fName);
            }
        }
    }
}

class cbe_list_manager
{
	function parseAndSave($c, $fName)
	{
		$c = iconv("cp1251", "koi8-r//TRANSLIT", $c);
		$lists = explode("\r\n��������������=��������� ���������\r\n", $c);
		$header = $lists[0];
		unset($lists[0]);

		$ll = array();

		foreach($lists as $c)
		{
			if(preg_match_all("/�������������=(\d+)\.(\d+)\.(\d+)/", $c, $o))
			{
				$data = $o[1][0]."-".$o[2][0]."-".$o[3][0];
			}else{
				preg_match_all("/�����������=(\d+)\.(\d+)\.(\d+)/", $c, $o);
				$data = $o[1][0]."-".$o[2][0]."-".$o[3][0];
			}

			if(!isset($ll[$data])) $ll[$data] = array();
			$ll[$data][] = $c;
		}

		foreach($ll as $data => $ls)
		{
			$c = $header;
			foreach($ls as $l)
			{
				$c .= "\r\n��������������=��������� ���������\r\n".$l;
			}

			$_fName = PAYMENTS_FILES_PATH.$fName.$data.".txt";
			$pFile = fopen($_fName, "wb");
			fwrite($pFile, iconv("koi8-r", "cp1251", $c));
			fclose($pFile);
			exec("chmod a+w ".$_fName);
		}
	}
}


class mt940
{
    private $c ="";
    private $payAcc = false;
    function __construct($c)
    {
        $this->c = explode("\n", str_replace("\r", "", $c));
    }

    function getPayAcc()
    {
        return $this->payAcc;
    }

    function getPays()
    {
        $c = $this->parseByTags($this->c);
        $c = $this->uncoverTags($c);
        //$c = $this->extractPays($c);
        usort($c, array("self", "sortBySum"));
        return $c;
    }

    function sortBySum($a, $b)
    {
        return ($a["sum"] == $b["sum"] ? 0 : ($a["sum"] > $b["sum"] ? 1 : 0));
    }

    function extractPays($c)
    {
        $pays = array();
        for($i = 0 ; $i < count($c["86"]) ; $i++)
        {
            $p = $c["86"][$i];

            foreach($c["61"][$i] as $k => $v)
            {
                $p[$k] = $v;
            }
            $pays[] = $p;
        }
        return $pays;
    }

    function uncoverTags(&$c)
    {
        $pays = array();
        if($c)
        {
            $pay = array();
            foreach ($c as $t)
            {
                if($t["code"] == 61)
                {
                    if($pay) $pays[] = $pay;
                    $pay = array();
                }

                switch($t["code"])
                {
                    case '25': $this->payAcc = $t["value"];break;
                    case '61': $this->_add($pay, $this->resolveTag_61($t["value"])); break;
                    case '86': $this->_add($pay, $this->resolveTag_86($t["value"])); break;
                }
            }
            if($pay) $pays[] = $pay;
        }

        return $pays;
    }

    function _add(&$a, $b)
    {
        if(!$a && $b)
            $a = $b;
        else
            foreach($b as $k => $v)
            {
                $a[$k] = $v;
            }
    }

    function resolveTag_86(&$vv)
    {

        //[3] => /PT/FT/PY/(ДМУ)Оплата за услуги связи по счету от 31-12-2010 |г. По л/с 3722 Договор 333./BN/ОАО МГТС 40702810900000001900/AB/0|44525232 АКБ МБРР (ОАО)/AB3/Г. МОСКВА/AB4/40702810900000001900
        //[4] => /PT/FT/PY/Оплата по счету N 201012-2476 от  29.12.2010 г., аб|онентя?кая плата за услуги связи в декабре 2010  г.(доступ в интер|нет, телея?онная  связь)/OB/044525716:40702810434000007815: 407028|10434000007815/OB3/ООО  Партнер СВ/BO/A.C40702810434000007815

        $d = array();
        /*
           foreach($vv as $v)
           {
         */
        $s = substr($vv, 10);
        $s = str_replace("|", "", $s);

        if(strpos($s, "/BN/") !== false)
        {
            $a = "";

            @list($descr, $p2) = explode("/BN/", $s);
            @list($company, $payAcc) = explode("/AB/", $p2);
            preg_match_all("/^(?P<bik>\d+) *(?P<bank>.*?) *(?P<pay_acc>\d+)$/", $payAcc, $oo,  PREG_SET_ORDER);
            $bank = preg_replace("@/AB\d/@", "", @$oo[0]["bank"]);
            $company = str_replace(" ".@$oo[0]["pay_acc"], "",$company);
            $o = array("",@$oo[0]["bik"],@$oo[0]["pay_acc"],$bank,$company);
            //(ДМУ)Оплата за услуги связи по счету от 31-12-2010 г. По л/с 3722 Договор 333./BN/ОАО МГТС 40702810900000001900/AB/0|44525232 АКБ МБРР (ОАО)/AB3/Г. МОСКВА/AB4/40702810900000001900
            //
        }else{

            @list($descr, $p2) = explode("/OB/", $s);
            @list($from, $a) = explode("/BO/", $p2);

            if(preg_match_all("/(\d+):(\d+): (\d+)\/OB3\/(.*)/", $from, $o, PREG_SET_ORDER)){
                $o = $o[0];
                $o[4] = preg_replace("@/OB\d/@", "", $o[4]);
            }else{
                $o = array("","","","", "");
            }
        }
        //$d[] =
        return array("description" => $descr, "from" => array("bik" => $o[1],"account" => $o[2], "a2" => $o[3]),"company" => $o[4], "A" => $a);
        //}
        return $d;
    }

    function resolveTag_61(&$vv)
    {
        $d = array();
        //1012291229CB695,44NTRF00169/044525388//372112760
        //    /CTC/066/INCOMING TRANSFER

        // [0] => 1012311231CB121,65NTRF+SB1012308EQBER//375003284|/CTC/066/INCOMING TRANSFER
        // [1] => 1012311231CB1400,00NTRF00042/044525986//374074494|/CTC/066/INCOMING TRANSFER


        /*
        foreach($vv as $v)
        {
        */
            /*
               [0] => 1012291229CB16120,00NTRF00237/046577859//372128663
               /CTC/066/INCOMING TRANSFER
               [1] => 10
               [2] => 12
               [3] => 29
               [4] => 12
               [5] => 29
               [6] => C
               [7] => B
               [8] => 16120,00
               [9] => TRF
               [10] => 00237/046577859
               [11] => 372128663
               [12] => CTC
               [13] => 066
               [14] => INCOMING TRANSFER
               )
             */

            preg_match_all("/^(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})(.)(.)([0-9,]+)N(.{3})(.*?)\/\/([^|]+)\|\/(.{3})\/(\d+)\/(.*)$/", $vv, $o, PREG_SET_ORDER);
            $o = $o[0];

            list($no,) = explode("/", $o[10]);
            $r = array(
                    "date_exch" => "20".$o[1]."-".$o[2]."-".$o[3],
                    "date" => "20".$o[1]."-".$o[4]."-".$o[5],
                    "oper_date" => "20".$o[1]."-".$o[4]."-".$o[5],
                    "sum" => (float)(($o[6] =="D" ? "-" : "").str_replace(",",".",$o[8])),
                    "noref" => $no,
                    "no" => $o[11],
                    "transaction" => $o[14],
                    "description" => $o[14]
                    );
            return $r;
            $d[] = $r;
        //}
        return $d;
    }




    function parseByTags(&$c)
    {
        $sValue = "";
        $sCode = "";

        $d = array();
        $a = array();

        foreach($c as $l)
        {
            if(preg_match_all("/^:(?P<code>.{2,3}):(?P<value>.*)/", $l, $o))
            {
                if($sValue)
                {
                    if($sCode == "61" || $sCode == "86")
                    {
                        $d[$sCode][] = $sValue;
                    }else{
                        $d[$sCode] = $sValue;
                    }

                    $a[] = array("code" => $sCode, "value" => $sValue);
                    $sCode = $sValue = "";
                }

                $sValue = str_replace("\r", "", $o["value"][0]);
                $sCode = $o["code"][0];

            }else{
                $sValue .= "|".str_replace(array("\r", "\n"), "", $l);
            }
        }

        if($sValue)
        {
            if($sCode == 61 || $sCode == 86)
            {
                $d[$sCode][] = $sValue;
            }else{
                $d[$sCode] = $sValue;
            }
            $a[] = array("code" => $sCode, "value" => $sValue);
            $sCode = $sValue = "";
        }

        return $a;
    }
}

