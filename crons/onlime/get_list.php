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
            Москва, 9-я Парковая улица, м. �?змайловская, д. 49, корп. 1, кв 22
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



//$db->Query("truncate onlime_delivery");
//$db->Query("truncate onlime_order");


$xml = new SimpleXMLElement($f);

$orders = OnlimeParserXML::parse($xml);
$checkedOrders = OnlimeCheckOrders::check($orders);




//_saveOrder

// process
$toAdd = array();
$toDecline = array();

echo date("r");
$count = 0;
foreach($checkedOrders as $order)
{
    $count++;
    //if($count > 1) continue;;
    
    $r = OnlimeOrder::find_by_external_id($order["order"]["id"]);

    if($r) continue;

    $id = $order["order"]["id"];
    echo "\n----------------------------id: ".$id."\n";
    print_r($order["error"]);

    $dbObj = OnlimeOrder::saveOrder($order["order"], $order["error"]);

    if($order["error"])
    {
        $id = "reject".$order["order"]["id"];
        $status = OnlimeRequest::STATUS_REJECT;
        $errorMsg = $order["error"]["message"];
        echo "\nnew: ".$order["order"]["id"]." => ".$order["error"]["status"].": ".$order["error"]["message"];
    }else{
        $id = "new: ok".$order["order"]["id"];
        $toAdd[] = $order;
        $status = OnlimeRequest::STATUS_NOT_DELIVERY;
        $errorMsg = "";
        echo "\n".$order["order"]["id"].": ".$order["order"]["fio"]." ===> ".$id;
    }

    if($dbObj)
    {
        $dbObj->setStatus($status, $errorMsg);
        $dbObj->setInternalId($id);
    }else{
        echo "\n!!! order[obj]: id[".$order["id"]."] not found";
    }
}


foreach(OnlimeOrder::find("all", array("conditions" => array("stage = ?", OnlimeOrder::STAGE_NEW))) as $order)
{

    $id = $order->external_id;;
    $intId = $order->bill_no;
    if($order->status == OnlimeRequest::STATUS_NOT_DELIVERY) //normal order, need save
    {
        $intId = Onlime1CCreateBill::create(unserialize(Encoding::toUtf8($order->order_serialize)));
        $order->setInternalId($intId);
    }
    $order->setStage(OnlimeOrder::STAGE_ADDED);
    echo "\nadded: ".$id." => ".$intId;
}

foreach(OnlimeOrder::find("all", array("conditions" => array("stage = ?", OnlimeOrder::STAGE_ADDED))) as $order)
{
    echo "\nanswer: ".$order->id;
    OnlimeRequest::post($order->external_id, $order->bill_no, $order->status, $order->error);

    $order->setStage(OnlimeOrder::STAGE_ANSWERED);
}

