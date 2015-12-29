<?php
define('NO_WEB',1);
define('PATH_TO_ROOT','../');
include PATH_TO_ROOT."conf_yii.php";


/*
Мишкольц    36 46 000-000   53  Стандартные 3646 +
Дебрецен    36 52 000-000   54  Стандартные 3652 +
Сегед       36 62 000-000   55  Стандартные 3662 +
Печ         36 72 000-000   56  Стандартные 3672 +
Дьёр        36 96 000-000   57  Стандартные 3696 +
 */


//78633090500 - 78633090999

//78312350500-78312351499

$region = 88;
$cityId = "7831";
$prefix = "78312350";


$q = "delete from voip_numbers where region = '".$region."' and number like '".$prefix."%'";
echo $q;
//$db->Query($q);

$sql = "";
for($i=500;$i<=999;$i++)
{
    $num = $prefix.str_pad($i, 11-strlen($prefix), "0", STR_PAD_LEFT); 
    echo "\n".$num;
    $sql .= ($sql ? "," : "").'("'.$num.'",'.$region.', '.$cityId.')';
}

$db->Query('insert into voip_numbers(number,region, city_id) values'.$sql);



$region = 88;
$cityId = "7831";
$prefix = "78312351";


$q = "delete from voip_numbers where region = '".$region."' and number like '".$prefix."%'";
echo $q;
//$db->Query($q);

$sql = "";
for($i=0;$i<=499;$i++)
{
    $num = $prefix.str_pad($i, 11-strlen($prefix), "0", STR_PAD_LEFT); 
    echo "\n".$num;
    $sql .= ($sql ? "," : "").'("'.$num.'",'.$region.', '.$cityId.')';
}

$db->Query('insert into voip_numbers(number,region, city_id) values'.$sql);

echo "OK\n";

exit();
