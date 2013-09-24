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
//include "post.php";
include "create.php";

$f = <<<EOF
<orders>
    <order_item>
        <id>116</id>
        <date>2013-09-13 17:33:06</date>
        <name>Кузнецов Александр Николаевич</name>
        <address>
        Москва, м. Свиблово, улица Лётчика Бабушкина, д.444, кв. 1111
        </address>
        <delivery_cost>0</delivery_cost>
        <delivery_date>2013-09-15</delivery_date>
        <delivery_time time_from="10:00" time_to="16:00"/>
        <comment>от метро автобус 185, 176 или пешком 15мин</comment>
        <request_status>0</request_status>
        <request_text/>
        <phones>
            <cell>903 1112223</cell>
        </phones>
        <coupon groupon="111" seccode="222" vercode="333"/>
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

echo "\n".date("r");
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
        $intId = Onlime1CCreateBill::create(Encoding::toUtf8(unserialize($order->order_serialize)));
        $order->setInternalId($intId);
    }
    $order->setStage(OnlimeOrder::STAGE_ADDED);
    echo "\nadded: ".$id." => ".$intId;
}

foreach(OnlimeOrder::find("all", array("conditions" => array("stage = ?", OnlimeOrder::STAGE_ADDED))) as $order)
{
    echo "\nanswer: ".$order->id;
    $answer = OnlimeRequest::post($order->external_id, $order->bill_no, $order->status, $order->error);
    echo $answer;

    $order->setStage(OnlimeOrder::STAGE_ANSWERED);
}

