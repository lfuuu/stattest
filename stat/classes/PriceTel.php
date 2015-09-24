<?php

class PriceTel
{
    static function view()
    {
        global $design, $db;
        $data = array(
            "990" => array("city" => "Москва (старый прайс)", "time" => false),
            "991" => array("city" => "Присоединение сетей", "time" => false),
        );
        foreach($db->AllRecords('select id, name from regions order by id desc', 'id') as $r)
            $data[$r["id"]] = array("city" => $r["name"], "time" => false);

        foreach (glob(STORE_PATH.'contracts/region_*.html') as $s) {
            if(preg_match("/\d+/", $s, $o))
            {
                $region = $o[0];
                $data[$region]["time"] = date("Y-m-d H:i", filemtime($s));
            }
        }
        $design->assign("data", $data);
        $design->AddMain('tarifs/price_tel.htm');
    }
    static function gen()
    {
        global $design;
        $region = get_param_integer("region", 0);
        if(!$region)
            die("Ошибка");
        $pp = array();
        foreach(array(5,4,3,2,1) as $p)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://stat.mcn.ru/operator/get_prices.php?region=$region&dest=$p");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \yii\base\Exception($error);
            }
            curl_close($ch);
            $pp[$p] = explode("\n", $result);
            //file_put_contents("/tmp/a".$p.".dat", serialize($pp[$p]));
            //$pp[$p] = unserialize(file_get_contents("/tmp/a".$p.".dat"));
        }
        $d = array();
        if($region != 990 && $region != 991)
        {
            self::__addTitle("Местные стационарные", $d);
            self::__parsePrice($pp[4], $d);
            self::__addTitle("Местные мобильные", $d);
            self::__parsePrice($pp[5], $d);
        }
        self::__addTitle("Россия", $d);
        self::__parsePrice($pp[1], $d);
        self::__addTitle("Ближнее зарубежье", $d);
        self::__parsePrice($pp[3], $d);
        self::__addTitle("Дальнее зарубежье", $d);
        self::__parsePrice($pp[2], $d);
        $design->assign("d", $d);
        $design->assign("region", $region);
        echo ($region == 991) ? $design->display("tarifs/price_tel__gen991.htm") : $design->display("tarifs/price_tel__gen.htm");
        exit();
    }
    static function save()
    {
        $region = get_param_integer("region", 0);
        if(!$region)
        {
            echo "Ошибка";
            exit();
        }
        if(file_put_contents(STORE_PATH."contracts/region_".$region.".html", $_POST["html"]))
        {
            echo "ok";
        }else{
            echo "Ошибка сохранения";
        }
        exit();
    }
    static function __addTitle($title, &$d)
    {
        $d[] = array("type" => "title", "title" => $title);
    }
    static function __parsePrice(&$cc, &$d)
    {
        foreach($cc as $idx => $c)
        {
            if($idx == 0) continue;
            $c = trim($c);
            if ($c == '') continue;
            $aa = explode(";", $c);
            foreach($aa as &$a)
            {
                $a = str_replace("\"", "", $a);
            }
            $d[] = array(
                "type" => "price",
                "code1" => $aa[0],
                "code2" => $aa[1],
                "name" => $aa[2],
                "zone" => $aa[3],
                "price1" => $aa[4],
                "price2" => $aa[5],
                "price3" => $aa[6],
            );
        }
    }
}