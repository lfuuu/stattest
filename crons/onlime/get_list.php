<?php

define("PATH_TO_ROOT", "../../");

include PATH_TO_ROOT."conf.php";

/*
$f = file_get_contents("http://www.onlime.ru/shop_xml_export.php");
file_put_contents("/tmp/onlime_shop", $f);
exit();
*/


include "xml_parser.php";
include "checker.php";
include "limit.php";
include "post.php";
include "create.php";

$f = <<<EOF
<orders>
    <order_item>
        <id>68</id>
        <date>2013-08-27 20:58:35</date>
        <name>Саньков Кирилл Андреевич</name>
        <address>
            Москва, 9-я Парковая улица, м. Измайловская, д. 49, корп. 1, кв 22
        </address>
        <delivery>
            <cost>0</cost>
            <date>2013-08-29</date>
            <time time_from="10:00" time_to="15:00"/>
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

//$f = file_get_contents("/tmp/onlime_shop");
$f = file_get_contents("http://www.onlime.ru/shop_xml_export.php");


$xml = new SimpleXMLElement($f);

$orders = OnlimeParserXML::parse($xml);
$checkedOrders = OnlimeCheckOrders::check($orders);

//print_r($checkedOrders);

// process
$toAdd = array();
$toDecline = array();

foreach($checkedOrders as $order)
{
    if($order["error"])
    {
        $toDecline[] = $order;
    }else{
        $toAdd[] = $order;
    }
}

/*
foreach($toDecline as $order)
{
    //if($order["order"]["id"] != 35) continue;

    if($order["error"]["status"] != "ignore")
    {
        echo "\n".$order["order"]["id"]." => ".$order["error"]["status"].": ".$order["error"]["message"];

        OnlimeRequest::post($order["order"]["id"], "", OnlimeRequest::STATUS_REJECT, $order["error"]["message"]);
    }
}
*/

foreach($toAdd as $order)
{
    $o = $order["order"];
    if($o["id"] != 37) continue;


    $id = Onlime1CCreateBill::create($o);

    echo "\n".$o["id"].": ".$o["fio"]." ===> ".$id;;
    OnlimeRequest::post($order["order"]["id"], $id, OnlimeRequest::STATUS_NOT_DELIVERY);
    exit();
}

