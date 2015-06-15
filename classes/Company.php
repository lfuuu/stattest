<?php
namespace app\classes;
class Company
{
    public static function getProperty($firma, $time = null)
    {
        $billDate = self::_resolveDate($time);
        $firma = $firma ? $firma : "mcn";
        $firms = array(
            "mcn_telekom" => array(
                "name_full" => "Общество с ограниченной ответственностью &laquo;МСН Телеком&raquo;",
                "name" => "ООО &laquo;МСН Телеком&raquo;",
                "address" => "115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8",
                "post_address" => "115162, г. Москва, а/я &#8470;21",
                "inn" => "7727752084",
                "kpp" => "772401001",
                "acc" => "40702810038110015462",
                "bank" => "Московский банк Сбербанка России ОАО, г.Москва",
                "kor_acc" => "30101810400000000225",
                "bik" => "044525225",
                "phone" => "(495) 950-56-78",
                "fax" => "(495) 638-50-17",
                "email" => "info@mcn.ru",
                //"director" => "Мельников А. К.",
                //"director_" => "Мельникова А. К.",
                "director" => "Надточеева Н. А.",
                "director_" => "Надточеевой Н. А. ",
                "director_post" => "Генеральный директор",
                "director_post_" => "Генерального директора",
                "logo" => "mcntelecom-logo.png",
            ),
            /*
            "mcn_telekom_hungary" => [
                "name" => "MCN Telecom Kft.",
                "address" => "123098, Moscow, ulitsa Akademika Bochvara, 10B",
                "post_address" => "115487, Moscow, 2-y Nagatinsky proyezd, 2с8",
                "inn" => "7727752084",
                "kpp" => "772401001",
                "acc" => "40702810038110015462",
                "bank" => "Oroszország, Szberbank zRt., Moszkvai fiok",
                "kor_acc" => "30101810400000000225",
                "bik" => "SABRRUMM",
                "phone" => "+7 495 105-99-99",
                "fax" => "+7 495 105-99-96",
                "email" => "info@mcn.ru",
                "director" => "",
                "director_" => "",
                "director_post" => "",
                "director_post_" => ""
            ],
*/
            "mcn_telekom_hungary" => [
                "name" => "MCN Telecom Kft.",
                "address" => "123098, Moscow, ulitsa Akademika Bochvara, 10B",
                "post_address" => "115487, Moscow, 2-y Nagatinsky proyezd, 2с8",
                "inn" => "7727752084",
                "reg_no" => "1117746441647", //ogrn
                "acc" => "40702810038110015462",
                "bank" => "Oroszország, Szberbank zRt., Moszkvai fiok",
                "swift" => "SABRRUMM",
                "phone" => "+7 495 105-99-99",
                "fax" => "+7 495 105-99-96",
                "email" => "info@mcn.ru",
                "logo" => "mcntelecom-logo.png",
            ],
            "tel2tel_hungary" => [
                "name" => "Tel2tel Kft.",
                "address" => "Budapest, 1114, Kemenes utca 8. félemelet 3.  Magyarorsag",
                "post_address" => "Budapest, 1114, Kemenes utca 8. félemelet 3.  Magyarorsag",
                "inn" => "12773246-2-43 / HU12773246",
                "reg_no" => "01 09 702746",
                "acc" => "12010611- 01424475 - 00100006 Ft,
                              12010611 - 01424475 - 00300000 Usd,
                              12010611 - 01424475 - 00200003 Euro",
                "bank" => "Raiffeisen Bank Zrt.",
                "swift" => "UBRTHUHB",
                "phone" => "+36 1 490-0999",
                "fax" => "+36 1 490-0998",
                "email" => "info@tel2tel.com",
                "logo" => "tel2tel.png",
                "site" => "www.tel2tel.com",
            ],
            "ooomcn" => array(
                "name_name" => "Общество с ограниченной ответственностью &laquo;МСН&raquo;",
                "name" => "ООО &laquo;МСН&raquo;",
                "address" => "117574 г. Москва, Одоевского пр-д., д. 3, кор. 7",
                "post_address" => "115162, г. Москва, а/я &#8470;21",
                "inn" => "7728638151",
                "kpp" => "772801001",
                "acc" => "40702810538110011157",
                "bank" => "Московский банк Сбербанка России ОАО, г. Москва",
                "kor_acc" => "30101810400000000225",
                "bik" => "044525225",
                "phone" => "(495) 950-56-78 доп. 159",
                "fax" => "(495) 638-50-17",
                "email" => "info@mcn.ru",
                "director" => "Бирюкова Н. В.",
                "director_" => "Бирюковой Н. В.",
                "director_post" => "Генеральный директор",
                "director_post_" => "Генерального директора"
            ),
            "mcn" => array(
                "name_name" => "Общество с ограниченной ответственностью &laquo;Эм Си Эн&raquo;",
                "name" => "ООО &laquo;Эм Си Эн&raquo;",
                "address" => "113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130",
                "post_address" => "115162, г. Москва, а/я &#8470;21",
                "inn" => "7727508671",
                "kpp" => "772701001",
                "acc" => "40702810600301422002",
                "bank" => "ЗАО КБ &laquo;Ситибанк&raquo;",
                "kor_acc" => "30101810300000000202",
                "bik" => "044525202",
                "phone" => "(495) 950-56-78",
                "fax" => "(495) 638-50-17",
                "email" => "info@mcn.ru",
                "director" => "Мельников А. К.",
                "director_" => "Мельникова А. К.",
                "director_post" => "Генеральный директор",
                "director_post_" => "Генерального директора",
                "logo" => "logo2.gif",
            ),
            "mcm" => array(
                "name_full" => "Общество с ограниченной ответственностью &laquo;МСМ&raquo;",
                "name" => "ООО &laquo;МСМ&raquo;",
                "address" => "117218, г. Москва, ул. Б. Черемушкинская, д. 25, стр. 97",
                "inn" => "7727667833",
                "kpp" => "772701001",
                "acc" => "40702810500540001425",
                "bank" => "ОАО &laquo;БАНК УРАЛСИБ&raquo;",
                "kor_acc" => "30101810100000000787",
                "bik" => "044525787",
                "phone" => "(495) 950-58-41",
                "email" => "arenda@mcn.ru",
                "director" => "Мельников Е. И.",
                "director_" => "Мельникова Е. И.",
                "director_post" => "Директор",
                "director_post_" => "Директора"
            ),
            "ooocmc" => array(
                "name_full" => "Общество с ограниченной ответственностью &laquo;Си Эм Си&raquo;",
                "name" => "ООО &laquo;Си Эм Си&raquo;",
                "address" => "117218, г. Москва, ул. Б. Черемушкинская, д. 25, стр. 97",
                "inn" => "7727701308",
                "kpp" => "772701001",
                "acc" => "40702810800540001507",
                "bank" => "ОАО &laquo;БАНК УРАЛСИБ&raquo;",
                "kor_acc" => "30101810100000000787",
                "bik" => "044525787",
                "phone" => "(495) 950-58-41",
                //"fax" => "(499) 123-55-33",
                "email" => "arenda@mcn.ru",
                "director" => "Надточеева Н. А.",
                "director_" => "Надточееву Н. А. ",
                "director_post" => "Заместитель Генерального директора",
                "director_post_" => "Заместителя Генерального директора"
            ),
            "all4geo" => array(
                "name_full" => "Общество с ограниченной ответственностью &laquo;Олфогео&raquo;",
                "name" => "ООО &laquo;Олфогео&raquo;",
                "address" => "115487, г. Москва, Нагатинский 2-й проезд, дом 2, строение 8",
                "inn" => "7727752091",
                "kpp" => "772401001",
                "acc" => "40702810038110016607",
                "bank" => "ОАО Сбербанк России",
                "kor_acc" => "30101810400000000225",
                "bik" => "044525225",
                //"phone" => "(495) 950-58-41",
                //"fax" => "(499) 123-55-33",
                //"email" => "arenda@mcn.ru",
                "director" => "Котельникова О. И.",
                "director_" => "Котельникову О. И.",
                "director_post" => "Генеральный директор",
                "director_post_" => "Генеральный директор"
            ),
            "all4net" => array(
                "name_full" => "Общество с ограниченной ответственностью &laquo;Олфонет&raquo;",
                "name" => "ООО &laquo;Олфонет&raquo;",
                "address" => "117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97",
                "inn" => "7727731060",
                "kpp" => "772701001",
                "acc" => "40702810500540000002",
                "bank" => "ОАО \"УРАЛСИБ\"",
                "kor_acc" => "30101810100000000787",
                "bik" => "044525787",
                "phone" => "(495) 638-77-77",
                //"fax" => "(499) 123-55-33",
                //"email" => "arenda@mcn.ru",
                "director" => "",
                "director_" => "",
                "director_post" => "Генеральный директор",
                "director_post_" => "Генеральный директор",
                "logo" => "logo_all4net.gif",
            ),
            "wellstart" => array(
                "name_full" => "Общество с ограниченной ответственностью &laquo;Веллстарт&raquo;",
                "name" => "ООО &laquo;Веллстарт&raquo;",
                "address" => "115487, Москва, 2-й Нагатинский проезд, д.2, стр.8",
                "inn" => "7724899307",
                "kpp" => "772401001",
                "acc" => "40702810038110020279",
                "bank" => "ОАО СБЕРБАНК РОССИИ",
                "kor_acc" => "30101810400000000225",
                "bik" => "044525225",
                "phone" => "(495) 950-56-78",
                //"fax" => "(499) 123-55-33",
                //"email" => "arenda@mcn.ru",
                "director" => "Полуторнова Т. В.",
                "director_" => "Полуторнову Т. В.",
                "director_post" => "Генеральный директор",
                "director_post_" => "Генеральный директор"
            ),
        );
        // correcting, new values
        if ($firma == "mcn_telekom")
        {
            if ($billDate >= strtotime("2013-12-20"))
            {
                $f = & $firms["mcn_telekom"];
                $f["kpp"] = "773401001";
                $f["address"] = "123098, г.Москва, ул. Академика Бочвара, д.10Б";
            }
            if ($billDate >= strtotime("2015-01-01"))
            {
                $f["director"] = "Пыцкая М. А.";
                $f["director_"] = "Пыцкой М. А.";
            }
        }
        if ($firma == "all4net")
        {
            if ($billDate >= strtotime("2013-08-13"))
            {
                $firms["all4net"]["address"] = "117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130";
            }
        }
        if ($billDate >= strtotime("2014-08-26"))
        {
            switch ($firma)
            {
                case 'mcn':
                    $firms['mcn']["phone"] =  '(495) 105-99-99';
                    break;
                case 'mcn_telekom':
                    $firms['mcn']["phone"] =  '(495) 105-99-99';
                    break;
                case 'wellstart':
                    $firms['mcn']["phone"] =  '(495) 105-99-99';
                    break;
                case 'all4net':
                    $firms['mcn']["phone"] =  '(495) 105-99-97';
                    break;
            }
            if (isset($firms[$firma]['fax']))
            {
                $firms[$firma]['fax'] = '(495) 105-99-96';
            }
        }
        return $firms[$firma];
    }
    public static function getDetail($firma, $time = null)
    {
        $f = self::getProperty($firma, $time);
        $d = $f["name"]."<br /> Юридический адрес: ".$f["address"].
            (isset($f["post_address"]) ? "<br /> Почтовый адрес: ".$f["post_address"] : "").
            "<br /> ИНН ".$f["inn"].", КПП ".$f["kpp"]."<br /> Банковские реквизиты:<br /> р/с:&nbsp;".$f["acc"]." в ".$f["bank"]."<br /> к/с:&nbsp;".$f["kor_acc"]."<br /> БИК:&nbsp;".$f["bik"]."<br /> телефон: ".$f["phone"].(isset($f["fax"]) && $f["fax"] ? "<br /> факс: ".$f["fax"] : "")."<br /> е-mail: ".$f["email"];
        return $d;
    }
    private static function _resolveDate($bill_or_time)
    {
        if ($bill_or_time === null)
        {
            $billDate = time();
        } elseif (is_array($bill_or_time) && isset($bill_or_time["bill_date"])) {
            $billDate = strtotime($bill_or_time["bill_date"]);
        } elseif (preg_match("/^\d+$/", $bill_or_time)) { //timestamp
            $billDate = $bill_or_time;
        } elseif (preg_match("/\d{4}-\d{2}-\d{2}/", $bill_or_time)) { // date
            $billDate = strtotime($bill_or_time);
        } else {
            $billDate = time();
        }
        return $billDate;
    }
    public static function setResidents($firma, $bill_or_time = null)
    {
        $isGenDir = null;
        if(!$firma)
        {
            $firma = "mcn_telekom";
        }
        $billDate = self::_resolveDate($bill_or_time);
        if($firma == "all4geo")
        {
            $b = "kot_oi";
        }elseif($firma == "mcm")
        {
            $b = "mel_ei";
        }elseif ($firma == 'markomnet_new'){
            $b = "maz";
            if($billDate >= strtotime("2011-12-01") && $billDate <= strtotime("2012-03-31"))
                $b = "usk";
        }elseif ($firma == 'ooocmc')
        {
            if($billDate >= strtotime("2012-11-16"))
            {
                $b = "lgm";
            }else{
                $b = "usk";
            };
        }elseif($firma == 'ooomcn')
        {
            if($billDate >= strtotime("2012-11-16"))
            {
                $b = "lgm";
            }elseif($billDate >= strtotime("2010-10-01"))
            {
                $b = "usk";
            }else{
                $b = "pol";
            }
        }elseif($firma == 'all4net' && $billDate > strtotime("2011-04-01")){
            $b = "nem";
        }elseif($billDate > strtotime("2008-03-31"))
        {
            $b = "ant";
        }else{
            $b = "pol";
        }
        if($firma == "all4geo")
        {
            $d = "kot_oi";
        }elseif ($firma == 'ooomcn' && $billDate > strtotime("2010-05-04"))
        {
            $d = "bnv";
        }elseif($firma == "all4net")
        {
            $d = "pma";
            if($billDate >= strtotime("2015-01-01"))
            {
                $d = "kor";
            }
        }elseif($firma == "mcn_telekom")
        {
            $d = $b = "vav";
            if($billDate >= strtotime("2012-04-01"))
            {
                $d = "mak";
                $b = "ant";
            }
            if($billDate >= strtotime("2013-07-31"))
            {
                $d = "nat";
            }
            if($billDate >= strtotime("2015-01-01"))
            {
                $d = "pma";
                $isGenDir = true;
            }
            if ($billDate >= strtotime("2015-06-01"))
            {
                $b = "sim";
            }
        }elseif ($firma == "markomnet_new")
        {
            $d = "maz";
        }elseif ($firma == "markomnet_service")
        {
            $b = $d = "udi";
            if($billDate >= strtotime("2012-10-01"))
            {
                $b =$d ="mel_du";
            }
        }elseif ($firma == "mcm")
        {
            $d = "mel_ei";
        }elseif ($firma == "ooocmc" && $billDate >= strtotime("2012-01-01") && $billDate < strtotime("2013-03-01"))
        {
            $d = "nat";
        }else{
            $d = "mak";
        }
        if ($firma == "wellstart")
        {
            $d = $b = "pol_tv";
        }
        $u = array(
            "bnv" => array(
                "name" => "Бирюкова Н.В.",
                "name_" => "Бирюковой Н.В.",
                "position" => "Директор",
                "position_" => "Директора",
                "sign" => array("src" => "sign_bnv.png", "width" => 140, "height" => 142)),
            "pma" => array(
                "name" => "Пыцкая М. А.",
                "name_" => "Пыцкой М. А.",
                "position" => "Директор",
                "position_" => "Директора",
                "sign" => array("src" => "sign_pma.png", "width" => false)),
            "vav" => array(
                "name" => "Вавилова Я. В.",
                "name_" => "Вавиловой Я. В.",
                "position" => "Генеральный директор",
                "position_" => "Генерального директора",
                "sign" => array("src" => "sign_vav.png", "width" => 140, "height" => 142)),
            "maz" => array(
                "name" => "Мазур Т. В.",
                "name_" => "Мазур Т. В.",
                "position" => "Генеральный директор",
                "position_" => "Генерального директора",
                "sign" => false),
            "mak" => array(
                "name" => "Мельников&nbsp;А.&nbsp;К.",
                "name_" => "Мельникова А. К.",
                "position" => "Генеральный директор",
                "position_" => "Генерального директора",
                "sign" => array("src" => "sign_mel.gif", "width" => 155, "height" => 80)),
            "kot_oi" => array(
                "name" => "Котельникова&nbsp;О.&nbsp;И.",
                "name_" => "Котельникову О. И.",
                "position" => "Генеральный директор",
                "position_" => "Генерального директора",
                "sign" => false),
            "nat" => array(
                "name" => "Надточеева Н. А.",
                "name_" => "Надточеевой Н. А.",
                "position" => "Генеральный директор",
                "position_" => "Генерального директора",
                "sign" => array("src" => "sign_nat.png", "width" => 152, "height" => 94)),
            "udi" => array(
                "name" => "Юдицкая Н. С.",
                "name_" => "Юдицкая Н. С.",
                "position" => "Генеральный директор",
                "position_" => "Генерального директора",
                "sign" => false),
            "mel_du" => array(
                "name" => "Мельников Д.",
                "name_" => "Мельникова Д.",
                "position" => "Генеральный директор",
                "position_" => "Генерального директора",
                "sign" => false),
            "mel_ei" => array(
                "name" => "Мельников Е. И.",
                "name_" => "Мельникова Е. И.",
                "position" => "Директор",
                "position_" => "Директора",
                "sign" => false
            ),
            "pol_tv" => array(
                "name" => "Полуторнова Т. В.",
                "name_" => "Полуторнову Т. В.",
                "position" => "Генеральный директор",
                "position_" => "Генерального директора",
                "sign" => false
            ),
            "kor" => array(
                "name" => "Королева В.В.",
                "name_" => "Королеву В.В.",
                "position" => "Генеральный директор",
                "position_" => "Генерального директора",
                "sign" => array("src" => "sign_kor.png", "width" => 140, "height" => 88),
            ),
            "usk" => array(
                "name" => "Ускова М. С.",
                "sign" => array("src" => "sign_usk.png", "width" => 137, "height" => 123)),
            "pol" => array("name" => "Полехина Г. Н.",  "sign" => false),
            "lgm" => array("name" => "Лаврова Г. М.",  "sign" => false),
            "ant" => array(
                "name" => "Антонова Т. С.",
                "sign" => array("src" => "sign_ant.gif", "width" => 139, "height" => 35)
            ),
            "nem" => array(
                "name" => "Нем И. В.",
                "sign" => array("src" => "sign_nem.png", "width" => 140, "height" => 142)
            ),
            "sim" => ["name" => "Симоненко Т. Е.", "sign" => ["src" => "sign_sim.png", "width" => 140, "height" => 54]]
        );
        $firms = array(
            "all4net" => array(
                "src" => "stamp_all4net.jpg",
                "style" => "position:relative;left:-10;top:-260;z-index:-10; margin-bottom:-170px;",
                "name" => "ООО &laquo;Олфонет&raquo;",
                "width" => false
            ),
            "mcn" => array(
                "src" => "stampmcn.gif",
                "style" => "position:relative;left:-20;top:-180;z-index:-10; margin-bottom:-170px;",
                "name" => "ООО &laquo;Эм Си Эн&raquo;",
                "width"=>250*0.8, "height"=>211*0.8
            ),
            "mcn_telekom" => array(
                "src" => "stamp_mcn_telekom.png",
                "style" => "position:relative;left:40;top:-200;z-index:-10; margin-bottom:-170px;",
                "name" => "ООО &laquo;МСН Телеком&raquo;",
                "width"=>251*0.8, "height"=>256*0.8
            ),
            "ooocmc" => array(
                "src" => "stamp_si_em_si.png",
                "style" => "position:relative;left:-40;top:-240;z-index:-10; margin-bottom:-170px;",
                "name" => "ООО &laquo;Си Эм Си&raquo;",
                "width"=>250*0.8, "height"=>252*0.8
            ),
            "ooomcn" => array(
                "src" => "stamp_mcn.png",
                "style" => "position:relative;left:30;top:-260;z-index:-10; margin-bottom:-170px;",
                "name" => "ООО &laquo;МСН&raquo;",
                "width"=>250*0.8, "height"=>251*0.8
            ),
            "markomnet" => array("name" => "ООО &laquo;МАРКОМНЕТ&raquo;", "src" => false, "style" => "", "width" => false),
            "markomnet_new" => array("name" => "ООО &laquo;МАРКОМНЕТ&raquo;", "src" => false, "style" => "", "width" => false),
            "markomnet_service" => array("name" => "ООО &laquo;Маркомнет сервис&raquo;", "src" => false, "style" => "", "width" => false),
            "mcm" => array("name" => "ООО &laquo;МСМ&raquo;", "src" => false, "style" => "", "width" => false),
            "all4geo" => array("name" => "ООО &laquo;Олфогео&raquo;", "src" => false, "style" => "", "width" => false),
            "wellstart" => array("name" => "ООО &laquo;Веллстарт&raquo;", "src" => false, "style" => "", "width" => false),
        );
        global $design;

        $firm_buh = $u[$b];
        if (!isset($firm_buh["position"]))
            $firm_buh["position"] = "Главный бухгалтер";
        if ($isGenDir === true)
        {
            $u[$d]["position"] = "Генеральный директор";
            $u[$d]["position_"] = "Генерального директора";
        }
        if ($design) {
            $design->assign("firma", $firms[$firma]);
            $design->assign("firm_director", $u[$d]);
            $design->assign("firm_buh", $firm_buh);
        }

        return [
            'firma' => $firms[$firma],
            'firm_director' => $u[$d],
            'firm_buh' => $firm_buh
        ];
    }
}
