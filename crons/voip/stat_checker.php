<?php 
define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT."conf.php";

$regions = getVoipRegions();

$tmp = $err_log = array();
foreach ($regions as $region) {
    if(($cnt = checkRegionStat($region)) == 0) {
        $err_log[] = $region;
    }
    $tmp[$region] = $cnt;
}

if (count($err_log) == 0) return true;
else print_r($err_log);

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

    $st_date = date('Y-m-d', strtotime("-1 day"));
    $en_date = date('Y-m-d');

    $sql = "select 
                * 
            from 
                calls.calls_".$region." 
            where 
                ((time>='".$st_date."') AND (time<='".$en_date." 23:59:59'))
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
    foreach ($db->AllRecords('select distinct(region) from usage_voip order by region') as $r)
        $res[]=$r['region'];

    return $res;
}

?>