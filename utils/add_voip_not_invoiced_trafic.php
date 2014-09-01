<?php


echo date("r")."\n";
	define('NO_WEB',1);
	define('PATH_TO_ROOT','../');
    define('DEBUG_LEVEL', 0);
    require_once(PATH_TO_ROOT.'conf.php');


$f = file_get_contents("/home/adima/work/unbilled_voip_97_jul2014.sav");

$f = unserialize($f);

$k = array_keys($f);

$ss = array();

$sums = array();
foreach($f as $idx => $d)
{
    //if ($idx != 31745) continue;

    if ($d["sum_t"]["s"] != $d["sum_w"]["s"])
    {
        if ($d["sum_t"]["s"] - $d["sum_w"]["s"] <= 50) continue;

        $upSum = round($d["sum_t"]["s"] - $d["sum_w"]["s"], 2);
        $sums[] = $upSum;
        echo "\n".$idx.": (".$d["sum_t"]["c"]."/".$d["sum_w"]["c"].") ==> ".$d["sum_t"]["s"]." - ".$d["sum_w"]["s"]." = ".$upSum;

        apply($idx, calc($d));
    }
}

function apply($clientId, $d)
{
    print_r($d);

    $lastBill = getLastBill($clientId);

    if (!$lastBill)
    {
        echo "\n!!! счет не найден";
        return;
    }

    addInBill($clientId, $lastBill, $d);

}

function addInBill($clientId, $billNo, $d)
{
    global $db;

    $sum = 0;
    foreach($d as $item => $ll)
    {
        $value = $ll["lines_t"] - $ll["lines_w"];
        $sum += $value;

        $maxSort = $db->GetValue("select ifnull(max(sort), 0) from newbill_lines where bill_no = '".$billNo."'");

        echo "maxSort: ".$maxSort;

        $db->QueryInsert("newbill_lines", array(
                    "bill_no" => $billNo,
                    "sort" => $maxSort+1,
                    "item" => $item,
                    "price" => $value/1.18,
                    "sum" => $value,
                    "amount" => 1,
                    "date_from" => "2014-08-01",
                    "date_to" => "2014-08-31",
                    "type" => "service"
                    )
                );
    }

    $db->Query("update newbills set sum = sum + ".$sum." where bill_no = '".$billNo."'");
        
}

function getLastBill($clientId)
{
    global $db;
    return $db->GetValue("select bill_no from newbills where client_id = '".$clientId."' order by bill_date desc limit 1");
}



function calc($d)
{
    $ss = array();

    foreach(array("lines_w", "lines_t") as $section)
    {
        foreach($d[$section] as $l)
        {
            $ss[$l["item"]][$section] = $l["sum"];
        }
    }

    $nss = array();
    foreach($ss as $item => $ll)
    {
        if ($ll["lines_w"] != $ll["lines_t"])
            $nss[$item] = $ll;
    }


    return $nss;

}


