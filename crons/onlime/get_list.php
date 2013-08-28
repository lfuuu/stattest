<?php


//$f = file_get_contents("http://www.onlime.ru/shop_xml_export.php");
//file_put_contents("/tmp/onlime_shop", $f);
//$f = file_get_contents("/tmp/onlime_shop");

$f = <<<EOF
<orders>
<order_item>
<id>68</id>
<date>2013-08-27 20:58:35</date>
<name>Саньков Кирилл Андреевич</name>
<address>
Москва, 9-я Парковая улица | Измайловская | д. 49, корп. 1 | 22
</address>
<delivery>
<cost>0</cost>
<date>2013-08-29</date>
<time time_from="10:00" time_to="15:00"/>
<inside_mkad>1</inside_mkad>
</delivery>
<comment/>
<request_status>0</request_status>
<request_text/>
<phones>
<cell>926 3459974</cell>
</phones>
<order_product>
<product id="3" quantity="1" cost="2500" total="2500"/>
</order_product>
</order_item>
</orders>
EOF;



$xml = new SimpleXMLElement($f);
print_r($xml);

$orders = array();

if($m)

foreach($xml as $node)
{
    //
}
