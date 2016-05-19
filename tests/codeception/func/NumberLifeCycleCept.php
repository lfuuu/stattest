<?php

use app\models\filter\FreeNumberFilter;
use app\models\Number;
use app\models\UsageVoip;
use app\models\LogTarif;
use app\models\ClientAccount;

$I = new _FuncTester($scenario);
$I->wantTo('Test Number life cycle');

$transaction = Yii::$app->db->beginTransaction();

$clientAccount = createSingleClientAccount();
$I->assertNotNull($clientAccount);

$freeNumber =
    (new FreeNumberFilter)
        ->getNumbers()
        ->randomOne();

$number = Number::findOne($freeNumber->number);
$I->assertNotNull($number);
$I->assertEquals($number->status, Number::STATUS_INSTOCK);

// Резервируем номер
Number::dao()->startReserve($number, $clientAccount);
$number->refresh();
$I->assertEquals($number->status, Number::STATUS_RESERVED);
$I->assertEquals($number->client_id, $clientAccount->id);

// Отмена резервирования номера
Number::dao()->stopReserve($number);
$freeNumber->refresh();
checkInStock($I, $number);

// Размещение номера в отстойнике
Number::dao()->startHold($number);
$freeNumber->refresh();
checkHold($I, $number);

// Отмена размещения номера в отстойнике
Number::dao()->stopHold($number);
$number->refresh();
checkInStock($I, $number);

// Снятие номера с продажи
Number::dao()->startNotSell($number);
$number->refresh();
$I->assertEquals($number->status, Number::STATUS_NOTSELL);
$I->assertEquals($number->client_id, 764);

// Отмена снятия номера с продажи
Number::dao()->stopNotSell($number);
$number->refresh();
checkInStock($I, $number);

// Проверка существования услуги с номером
$now = new \DateTime('now', new \DateTimeZone('UTC'));
$usageVoip = new UsageVoip([
    'client' => $clientAccount->client,
    'actual_from' => $now->format('Y-m-d'),
    'actual_to' => $now->modify('+1 month')->format('Y-m-d'),
    'E164' => $number->number,
    'address' => 'test address'
]);
$I->assertTrue($usageVoip->save());
Number::dao()->actualizeStatus($number);
$number->refresh();

$I->assertEquals($number->status, Number::STATUS_ACTIVE);
$I->assertEquals($number->client_id, $clientAccount->id);
$I->assertEquals($number->usage_id, $usageVoip->id);

// Проверка актуальности статуса номера
$I->assertEquals($usageVoip->delete(), 1);
Number::dao()->actualizeStatus($number);
$number->refresh();
checkHold($I, $number);

// Проверка актуальности тарифного плана
$now = new \DateTime('now', new \DateTimeZone('UTC'));
$usageVoip = new UsageVoip([
    'client' => $clientAccount->client,
    'actual_from' => $now->format('Y-m-d'),
    'actual_to' => $now->modify('+1 month')->format('Y-m-d'),
    'E164' => $number->number,
    'address' => 'test address'
]);
$I->assertTrue($usageVoip->save());

$logTarif = new logTarif([
    'service' => 'usage_voip',
    'id_service' => $usageVoip->id,
    'id_tarif' => 624,
    'date_activation' => $now->format('Y-m-d')
]);
$I->assertTrue($logTarif->save());

Number::dao()->actualizeStatus($number);
$number->refresh();

$I->assertEquals($number->status, Number::STATUS_ACTIVE);
$I->assertEquals($number->client_id, $clientAccount->id);
$I->assertEquals($number->usage_id, $usageVoip->id);

$number->refresh();
$I->assertEquals($number->status, Number::STATUS_ACTIVE);

// Отмена активности услуги номера
$dFrom = new \DateTime('now', new \DateTimeZone('UTC'));
$dFrom->modify('-2 month');
$dTo = new \DateTime('now', new \DateTimeZone('UTC'));
$dTo->modify('-1 month');

$usageVoip->actual_from = $dFrom->format('Y-m-d');
$usageVoip->actual_to = $dTo->format('Y-m-d');

$logTarif->date_activation = $dFrom->format('Y-m-d');
$I->assertTrue($logTarif->save());
$I->assertTrue($usageVoip->save()); // актуализация статуса через поведение

$number->refresh();

checkInStock($I, $number);

$I->assertEquals($usageVoip->delete(), 1);
$I->assertEquals($logTarif->delete(), 1);

// Восстановление номера из отстойника
$now = new \DateTime('now', new \DateTimeZone('UTC'));
$m1 = new \DateTime('now', new \DateTimeZone('UTC'));
$m1->modify('+1 month');

$m2 = new \DateTime('now', new \DateTimeZone('UTC'));
$m2->modify('-1 sec');

$number->status = Number::STATUS_HOLD;
$number->hold_from = $now->format('Y-m-d H:i:s');
$number->hold_to = $m1->format('Y-m-d H:i:s');
$number->save();
\app\commands\NumberController::actionReleaseFromHold();

$number->refresh();
$I->assertEquals($number->status, Number::STATUS_HOLD);

$number->hold_to = $m2->format('Y-m-d H:i:s');
$number->save();
app\commands\NumberController::actionReleaseFromHold();
$number->refresh();
checkInStock($I, $number);

$transaction->rollBack();

/**
 * Создание болванки аккаунта
 * @return ClientAccount
 */
function createSingleClientAccount()
{
    $client = new ClientAccount;
    $client->is_active = 0;
    $client->validate();
    $client->save();
    $client->client = 'id' . $client->id;
    $client->save();
    return $client;
}

/**
 * Проверка номера на наличие в продаже
 *
 * @param _FuncTester $I
 * @param Number $number
 */
function checkInStock($I, Number $number)
{
    $I->assertEquals($number->status, Number::STATUS_INSTOCK);
    $I->assertNull($number->client_id);
    $I->assertNull($number->reserve_from);
    $I->assertNull($number->reserve_till);
    $I->assertNull($number->hold_to);
}

/**
 * Проверка номера в отстойнике
 *
 * @param _FuncTester $I
 * @param Number $number
 */
function checkHold($I, Number $number)
{
    $I->assertEquals($number->status, Number::STATUS_HOLD);
    $I->assertNotNull($number->hold_from);
    $I->assertNotNull($number->hold_to);

    $dtHoldFrom = new \DateTime($number->hold_from, new \DateTimeZone('UTC'));
    $I->assertNotNull($dtHoldFrom);

    $dtHoldTo = new \DateTime($number->hold_to, new \DateTimeZone('UTC'));
    $I->assertNotNull($dtHoldTo);

    $diff = $dtHoldFrom->diff($dtHoldTo);
    $I->assertNotNull($diff);
    $I->assertEquals($diff->m, 6); // 6 month
}