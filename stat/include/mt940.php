<?php

/**
 * Class banks
 */
class Banks
{
    static private $_names = array(
        "301422002" => array("citi_mcn", "citi"),
        "301423001" => array("citi_all4net_usd", "citi"),
        "301423002" => array("citi_all4net_rub", "citi"),
        "40702810500540000002" => array("ural_all4net", "ural"),
        "40702810800540001507" => array("ural_cmc", "ural"),
        "40702810700320000882" => array("mos_mcn", "mos"),
        "40702810038110015462" => array("sber_telekom", "sber"),
        "40702810038000034045" => array("sber_mcm", "sber"),
        '40702810338000213883' => ['sber_mcnservice', 'sber'],
    );

    /**
     * Распознование банка по заголовку выгрузки
     *
     * @param string $h
     * @return array|bool
     */
    public static function detect($h)
    {
        $h = str_replace(array("\n", "\r"), "", $h);
        $pa = array();
        foreach (self::$_names as $p => $v) {
            if (($pos = strpos($h, (string)$p)) !== false) {
                $pa[$pos] = $p;
            }
        }

        if ($pa) {
            $v = self::$_names[$pa[min(array_keys($pa))]];
            return array("file" => $v[0], "bank" => $v[1]);
        }

        return false;
    }

    /**
     * Получение названия файла
     *
     * @param string $p
     * @return mixed
     */
    public static function getFileName($p)
    {
        return self::$_names[$p][0];
    }

    /**
     * Получение банка
     *
     * @param string $p
     * @return mixed
     */
    public static function getBank($p)
    {
        return self::$_names[$p][1];
    }
}

/**
 * Class mt940ListManager
 */
class MT940ListManager
{
    /**
     * Распознование и сохранение выгрузки
     *
     * @param string $c
     */
    public static function parseAndSave($c)
    {
        $lists = explode("\r\n-\r\n", $c);
        if (count($lists) > 1) {
            unset($lists[count($lists) - 1]);
        }

        foreach ($lists as $c) {
            $m = new MT940($c);
            $p = $m->getPays();
            $payAcc = $m->getPayAcc();

            $fName = Banks::getFileName($payAcc);

            if ($p) {
                $d = $p[0]["date"];

                $fName = PAYMENTS_FILES_PATH . $fName . "__" . date("d-m-Y", strtotime($d)) . ".txt";
                $pFile = fopen($fName, "wb");
                fwrite($pFile, iconv("cp1251", "utf-8//TRANSLIT", $c));
                fclose($pFile);
                exec("chmod a+w " . $fName);
            }
        }
    }
}

/**
 * Class CbeListManager
 */
class CbeListManager
{
    /**
     * Распознование и сохранение выгрузки
     *
     * @param string $c
     * @param string $fName
     */
    public static function parseAndSave($c, $fName)
    {
        $c = iconv("cp1251", "utf-8//TRANSLIT", $c);
        $lists = explode("\r\nСекцияДокумент=Платежное поручение\r\n", $c);

        if (count($lists) > 1) {
            $header = $lists[0];
            unset($lists[0]);
        } else {
            $header = "";
        }

        $ll = array();

        foreach ($lists as $c) {
            if (preg_match_all("/ДатаПоступило=(\d+)\.(\d+)\.(\d+)/", $c, $o)) {
                $data = $o[1][0] . "-" . $o[2][0] . "-" . $o[3][0];
            } elseif (preg_match_all("/ДатаНачала=(\d+)\.(\d+)\.(\d+)/", $c, $o)) {
                $data = $o[1][0] . "-" . $o[2][0] . "-" . $o[3][0];
            } else {
                preg_match_all("/ДатаСписано=(\d+)\.(\d+)\.(\d+)/", $c, $o);
                $data = $o[1][0] . "-" . $o[2][0] . "-" . $o[3][0];
            }

            if (!isset($ll[$data])) {
                $ll[$data] = array();
            }

            $ll[$data][] = $c;
        }

        foreach ($ll as $data => $ls) {
            $c = $header;
            foreach ($ls as $l) {
                $c .= "\r\nСекцияДокумент=Платежное поручение\r\n" . $l;
            }

            $_fName = PAYMENTS_FILES_PATH . $fName . $data . ".txt";
            $pFile = fopen($_fName, "wb");
            fwrite($pFile, iconv("utf-8", "cp1251", $c));
            fclose($pFile);
            exec("chmod a+w " . $_fName);
        }
    }
}


class MT940
{
    private $_c = "";
    private $_payAcc = false;

    /**
     * MT940 constructor.
     *
     * @param string $c
     */
    public function __construct($c)
    {
        $this->_c = explode("\n", str_replace("\r", "", $c));
    }

    /**
     * Получение р/с
     *
     * @return bool
     */
    public function getPayAcc()
    {
        return $this->_payAcc;
    }

    /**
     * Получение платежей
     *
     * @return array
     */
    public function getPays()
    {
        $c = $this->parseByTags($this->_c);
        $c = $this->uncoverTags($c);
        usort($c, array("MT940", "sortBySum"));
        return $c;
    }

    /**
     * Сортировка
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public static function sortBySum($a, $b)
    {
        return ($a["sum"] == $b["sum"] ? 0 : ($a["sum"] > $b["sum"] ? 1 : 0));
    }

    /**
     * Разбор документа по тегам
     *
     * @param string $c
     * @return array
     */
    public function uncoverTags(&$c)
    {
        $pays = array();
        if ($c) {
            $pay = array();
            foreach ($c as $t) {
                if ($t["code"] == 61) {
                    if ($pay) {
                        $pays[] = $pay;
                    }

                    $pay = array();
                }

                switch ($t["code"]) {
                    case '25':
                        $this->_payAcc = $t["value"];
                        break;
                    case '61':
                        $this->_add($pay, $this->resolveTag61($t["value"]));
                        break;
                    case '86':
                        $this->_add($pay, $this->resolveTag86($t["value"]));
                        break;
                }
            }

            if ($pay) {
                $pays[] = $pay;
            }
        }

        return $pays;
    }

    /**
     * Добавление распознаных данных
     *
     * @param array $a
     * @param array $b
     */
    private function _add(&$a, $b)
    {
        if (!$a && $b) {
            $a = $b;
        } else {
            foreach ($b as $k => $v) {
                $a[$k] = $v;
            }
        }
    }

    /**
     * Распознование данных из тега 86
     *
     * @param string $vv
     * @return array
     */
    public function resolveTag86(&$vv)
    {
        $s = substr($vv, 10);
        $s = str_replace("|", "", $s);

        if (strpos($s, "/BN/") !== false) {
            $a = "";

            @list($descr, $p2) = explode("/BN/", $s);
            @list($company, $payAcc) = explode("/AB/", $p2);
            preg_match_all("/^(?P<bik>\d+) *(?P<bank>.*?) *(?P<pay_acc>\d+)$/", $payAcc, $oo, PREG_SET_ORDER);
            $bank = preg_replace("@/AB\d/@", "", @$oo[0]["bank"]);
            $company = str_replace(" " . @$oo[0]["pay_acc"], "", $company);
            $o = array("", @$oo[0]["bik"], @$oo[0]["pay_acc"], $bank, $company);
        } else {

            @list($descr, $p2) = explode("/OB/", $s);
            @list($from, $a) = explode("/BO/", $p2);

            if (preg_match_all("/(\d+):(\d+): (\d+)\/OB3\/(.*)/", $from, $o, PREG_SET_ORDER)) {
                $o = $o[0];
                $o[4] = preg_replace("@/OB\d/@", "", $o[4]);
            } else {
                $o = array("", "", "", "", "");
            }
        }

        return array(
            "description" => $descr,
            "from" => array("bik" => $o[1], "account" => $o[2], "a2" => $o[3]),
            "company" => $o[4],
            "A" => $a
        );
    }

    /**
     * Распознование данных из тега 61
     *
     * @param string $vv
     * @return array
     */
    public function resolveTag61(&$vv)
    {
        // 1012291229CB695,44NTRF00169/044525388//372112760
        // /CTC/066/INCOMING TRANSFER
        // [0] => 1012311231CB121,65NTRF+SB1012308EQBER//375003284|/CTC/066/INCOMING TRANSFER
        // [1] => 1012311231CB1400,00NTRF00042/044525986//374074494|/CTC/066/INCOMING TRANSFER
        // [0] => 1012291229CB16120,00NTRF00237/046577859//372128663
        // /CTC/066/INCOMING TRANSFER
        // [1] => 10
        // [2] => 12
        // [3] => 29
        // [4] => 12
        // [5] => 29
        // [6] => C
        // [7] => B
        // [8] => 16120,00
        // [9] => TRF
        // [10] => 00237/046577859
        // [11] => 372128663
        // [12] => CTC
        // [13] => 066
        // [14] => INCOMING TRANSFER
        // )
        preg_match_all("/^(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})(.)(.)([0-9,]+)N(.{3})(.*?)\/\/([^|]+)\|\/(.{3})\/(\d+)\/(.*)$/",
            $vv, $o, PREG_SET_ORDER);
        $o = $o[0];

        list($no,) = explode("/", $o[10]);

        return [
            "date_exch" => "20" . $o[1] . "-" . $o[2] . "-" . $o[3],
            "date" => "20" . $o[1] . "-" . $o[4] . "-" . $o[5],
            "oper_date" => "20" . $o[1] . "-" . $o[4] . "-" . $o[5],
            "sum" => (float)(($o[6] == "D" ? "-" : "") . str_replace(",", ".", $o[8])),
            "noref" => $no,
            "no" => $o[11],
            "transaction" => $o[14],
            "description" => $o[14]
        ];
    }


    /**
     * Парсинг документа на теги
     *
     * @param array $c
     * @return array
     */
    public function parseByTags(&$c)
    {
        $sValue = "";
        $sCode = "";

        $d = array();
        $a = array();

        foreach ($c as $l) {
            if (preg_match_all("/^:(?P<code>.{2,3}):(?P<value>.*)/", $l, $o)) {
                if ($sValue) {
                    if ($sCode == "61" || $sCode == "86") {
                        $d[$sCode][] = $sValue;
                    } else {
                        $d[$sCode] = $sValue;
                    }

                    $a[] = array("code" => $sCode, "value" => $sValue);
                    $sCode = $sValue = "";
                }

                $sValue = str_replace("\r", "", $o["value"][0]);
                $sCode = $o["code"][0];
            } else {
                $sValue .= "|" . str_replace(array("\r", "\n"), "", $l);
            }
        }

        if ($sValue) {
            if ($sCode == 61 || $sCode == 86) {
                $d[$sCode][] = $sValue;
            } else {
                $d[$sCode] = $sValue;
            }

            $a[] = array("code" => $sCode, "value" => $sValue);
            $sCode = $sValue = "";
        }

        return $a;
    }
}
