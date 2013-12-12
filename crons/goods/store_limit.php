<?php

define("PATH_TO_ROOT", "../../");
define("NO_WEB", 1);


include PATH_TO_ROOT."conf.php";

$conf = array(
        "13816" => 50,
        "15009" => 1000,
        "15362" => 100,
        "14733" => 1000,
        "14731" => 3000,
        "14732" => 5000,
        "14679" => 2000,
        "15078" => 1000,
        "3839" => 5000,
        "15840" => 1000,
        "14221" => 100,
        "15954" => 3000
        );


$goods = array();
foreach($db->AllRecords("
    SELECT 
        g.num_id as id, 
        g.name,
        ifnull(s.qty_store, 'null') as c 
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
    mail("adima123@yandex.ru", "[stat/goods/store:min_store_level] оповещение о снижении мимнимального остатка на складе", "Количество по следущим позициям меньше установленного уровня:\n\n\n".implode("\n", $goods) );
    mail("li@mcn.ru", "оповещение о снижении мимнимального остатка на складе", "Количество по следущим позициям меньше установленного уровня:\n\n\n".implode("\n", $goods) );
}



