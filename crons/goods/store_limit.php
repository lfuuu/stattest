<?php

define("PATH_TO_ROOT", "../../");
define("NO_WEB", 1);


include PATH_TO_ROOT."conf.php";

$conf = array(
        "13816" => 50,
        "15009" => 1000,
        "15362" => 100,
        "14733" => 5000,
        "14731" => 12000,
        "14732" => 17000,
        "14679" => 4000, //2.10.2014, 187237 (2000->4000)
        "15078" => 1000,
        "15982" => 1000,
        "14221" => 100,
        "15954" => 6000,
        "16471" => 20,
        "16061" => 5,
        "15038" => 10
        );


$goods = array();
foreach($db->AllRecords("
    SELECT 
        g.num_id as id, 
        g.name,
        ifnull(s.qty_free, 'null') as c 
    FROM 
        `g_goods` g
    INNER JOIN g_good_store s on (g.id = s.good_id)
    WHERE 
        num_id in ('".implode("','", array_keys($conf))."')") as $g)
{
    if(!isset($conf[$g["id"]])) continue;

    $min = $conf[$g["id"]];

    if($g["c"] < $min)
        $goods[$g["id"]] = "Позиция (id: ".$g["id"].") ".iconv("koi8-r", "utf-8", $g["name"]).". Количество на складе ниже ".$min.", составляет: ".$g["c"];
}


if ($goods)
{
    $headers = "Content-type: text/html; charset=utf-8";
    $subject = "оповещение о снижении мимнимального остатка на складе";
    $body = "Количество по следущим позициям меньше установленного уровня:<br><br>".implode("<br>", $goods);

    mail("vladimir@mcn.ru", $subject, $body , $headers);
}



