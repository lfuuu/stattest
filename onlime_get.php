<?php


//$f = file_get_contents("http://www.onlime.ru/shop_xml_export.php");
//file_put_contents("/tmp/onlime_shop", $f);
$f = file_get_contents("/tmp/onlime_shop");


$xml = new SimpleXMLElement($f);

print_r($xml);
