<?php 

use app\models\Number;
use app\models\UsageVoip;
use app\models\LogTarif;
use app\models\ClientAccount;

$I = new _FuncTester($scenario);
$I->wantTo('Test Number life cycle');



$accountId = ClientAccount::find()->select('max(id)')->scalar();//35800;
$account = ClientAccount::findOne(["id" => $accountId]);
$I->assertNotNull($account);

$numberNum = "74992130007";
$number = Number::findOne(["number" => $numberNum]);

$I->assertNotNull($number);
$I->assertEquals($number->status, Number::STATUS_INSTOCK);

//
//reserv. start.
//
Number::dao()->startReserve($number, $account);
$number->refresh();
$I->assertEquals($number->status, Number::STATUS_RESERVED);
$I->assertEquals($number->client_id, $account->id);

//
//reserv. stop.
//
Number::dao()->stopReserve($number);
$number->refresh();
function checkInStock($I, $number)
{
    $I->assertEquals($number->status, Number::STATUS_INSTOCK);
    $I->assertNull($number->client_id);
    $I->assertNull($number->reserve_from);
    $I->assertNull($number->reserve_till);
    $I->assertNull($number->hold_to);
}
checkInStock($I, $number);


//
//hold. start.
//
Number::dao()->startHold($number);
$number->refresh();

function checkHold($I, $number)
{
    $I->assertEquals($number->status, Number::STATUS_HOLD);
    $I->assertNotNull($number->hold_from);
    $I->assertNotNull($number->hold_to);

    $dtHoldFrom = new \DateTime($number->hold_from, new \DateTimeZone("UTC"));
    $I->assertNotNull($dtHoldFrom);

    $dtHoldTo = new \DateTime($number->hold_to, new \DateTimeZone("UTC"));
    $I->assertNotNull($dtHoldTo);

    $diff = $dtHoldFrom->diff($dtHoldTo);
    $I->assertNotNull($diff);
    $I->assertEquals($diff->m, 6); // 6 month
}
checkHold($I, $number);

//
//hold. end.
//
Number::dao()->stopHold($number);
$number->refresh();
checkInStock($I, $number);


//
//not sale. start.
//
Number::dao()->startNotSell($number);
$number->refresh();
$I->assertEquals($number->status, Number::STATUS_NOTSELL);
$I->assertEquals($number->client_id, 764);

//
//not sale. end.
//
Number::dao()->stopNotSell($number);
$number->refresh();
checkInStock($I, $number);


//
//active normal
//
$now = new \DateTime("now", new \DateTimeZone("UTC"));
$usagevoip = new UsageVoip( [
    "client" => $account->client,
    "actual_from" => $now->format("Y-m-d"),
    "actual_to" => $now->modify("+1 month")->format("Y-m-d"),
    "E164" => $number->number,
    "address" => "test address"
]);
$I->assertTrue($usagevoip->save());
Number::dao()->actualizeStatus($number);
$number->refresh();

$I->assertEquals($number->status, Number::STATUS_ACTIVE);
$I->assertEquals($number->client_id, $account->id);
$I->assertEquals($number->usage_id, $usagevoip->id);

//
//active. stop normal.
//
$I->assertEquals($usagevoip->delete(), 1);
Number::dao()->actualizeStatus($number);
$number->refresh();
checkHold($I, $number);

//
//active normal. test tarif
//
$now = new \DateTime("now", new \DateTimeZone("UTC"));
$usagevoip = new UsageVoip( [
    "client" => $account->client,
    "actual_from" => $now->format("Y-m-d"),
    "actual_to" => $now->modify("+1 month")->format("Y-m-d"),
    "E164" => $number->number,
    "address" => "test address"
]);
$I->assertTrue($usagevoip->save());

$logTarif = new logTarif( [
    "service" => "usage_voip",
    "id_service" =>$usagevoip->id,
    "id_tarif" => 624,
    "date_activation" => $now->format("Y-m-d")
] );
$I->assertTrue($logTarif->save());

Number::dao()->actualizeStatus($number);
$number->refresh();

$I->assertEquals($number->status, Number::STATUS_ACTIVE);
$I->assertEquals($number->client_id, $account->id);
$I->assertEquals($number->usage_id, $usagevoip->id);

$number->refresh();//aaaz
$I->assertEquals("aaaz0", "aaaz0");
$I->assertEquals($number->status, Number::STATUS_ACTIVE);

//
//active stop. test tarif
//
$dFrom = new \DateTime("now", new \DateTimeZone("UTC"));
$dFrom->modify("-2 month");
$dTo = new \DateTime("now", new \DateTimeZone("UTC"));
$dTo->modify("-1 month");

//adjustable
$usagevoip->actual_from = $dFrom->format("Y-m-d");
$usagevoip->actual_to = $dTo->format("Y-m-d");

$logTarif->date_activation = $dFrom->format("Y-m-d");
$I->assertTrue($logTarif->save());
$I->assertTrue($usagevoip->save()); //with start actualizeStatus by behavior


$number->refresh();

checkInStock($I, $number);

$I->assertEquals($usagevoip->delete(), 1);
$I->assertEquals($logTarif->delete(), 1);


//
//release from hold
//

$now = new \DateTime("now", new \DateTimeZone("UTC"));
$m1 = new \DateTime("now", new \DateTimeZone("UTC"));
$m1->modify("+1 month");

$m2 = new \DateTime("now", new \DateTimeZone("UTC"));
$m2->modify("-1 sec");

$number->status = Number::STATUS_HOLD;
$number->hold_from = $now->format("Y-m-d H:i:s");
$number->hold_to = $m1->format("Y-m-d H:i:s");
$number->save();
app\commands\NumberController::actionReleaseFromHold();

$number->refresh();
$I->assertEquals($number->status, Number::STATUS_HOLD);

$number->hold_to = $m2->format("Y-m-d H:i:s");
$number->save();
app\commands\NumberController::actionReleaseFromHold();
$number->refresh();
checkInStock($I, $number);





