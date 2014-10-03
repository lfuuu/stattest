<?php 
define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT."conf.php";

echo "\n".date("r");

$regions = getVoipRegions();

$tmp = $err_log = array();
foreach ($regions as $region) {
    if(($cnt = checkRegionStat($region)) == 0) {
        $err_log[] = $region;
    }
    $tmp[$region] = $cnt;
}

if (count($err_log) == 0) 
{
    echo "all ok\n";
} else {
    $str = array();

    foreach($db->AllRecords("select * from regions where id in ('".implode("','", $err_log)."')") as $r)
    {
        $str[] = $r["name"]." (".$r["id"].")";
    }

    print_r($str);

    $headers = "Content-type: text/html; charset=utf-8";
    mail(ADMIN_EMAIL, "[stat/voip/check_traf] VOIP трафик в регионах", "Статистика по телефонии за прошедшие сутки недоступна в следующих регионах: <br>".implode(", ", $str), $headers);
}

/** Функция проверяет, 
 * есть ли статистика в заданном регионе
 * 
 * @param string $region идентификатор региона
 * @return integer количество записей
 **/
function checkRegionStat($region = '')
{
    global $pg_db;

    if (!strlen($region)) return 0;

    $date = date('Y-m-d', strtotime("-1 day"));

    $sql = "select 
                * 
            from 
                calls.calls_".$region." 
            where 
                ((time>='".$date." 00:00:00') AND (time<='".$date." 23:59:59'))
            limit 1
            ";

    $pg_db->Query($sql);
    if ($pg_db->NumRows() == 0) return 0;

    return 1;
}

/** Функция получения регионов
 *
 * @return array массив регионов
 **/
function getVoipRegions()
{
    global $db;

    $res = array();
    foreach ($db->AllRecords('select distinct region from usage_voip where client not in ("mcn", "mcnvoip") order by region') as $r)
        $res[]=$r['region'];

    return $res;
}

?>
