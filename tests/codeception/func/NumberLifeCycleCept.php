<?php

use app\forms\usage\UsageVoipEditForm;
use app\models\filter\FreeNumberFilter;
use app\models\Number;
use app\models\UsageVoip;
use app\models\ClientAccount;

$I = new _FuncTester($scenario);
$I->wantTo('Test Number life cycle');

$now = new \DateTime('now', new \DateTimeZone(\app\helpers\DateTimeZoneHelper::TIMEZONE_MOSCOW));

$transaction = Yii::$app->db->beginTransaction();

// создаем ЛС
$clientAccount = createSingleClientAccount();
$I->assertNotNull($clientAccount);

$freeNumber =
    (new FreeNumberFilter)
        ->setRegions([$clientAccount->region])
        ->getNumbers()
        ->randomOne();

$number = Number::findOne($freeNumber->number);
$I->assertNotNull($number);
$I->assertEquals($number->status, Number::STATUS_INSTOCK);

// Резервируем номер
Number::dao()->startReserve($number, $clientAccount);
$number->refresh();
$I->assertEquals($number->status, Number::STATUS_NOTACTIVE_RESERVED);
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
$I->assertEquals($number->status, Number::STATUS_NOTSALE);
$I->assertEquals($number->client_id, 764);

// Отмена снятия номера с продажи
Number::dao()->stopNotSell($number);
$number->refresh();
checkInStock($I, $number);

// Работа с услугой

// Проверка существования услуги с номером

$usage = createUsage($I, $clientAccount, $number, 531 /*обычный публичный тариф */);

$I->assertNotNull($usage);
$I->assertNotNull($usage->tariff);
$I->assertNotEmpty($usage->tariff->name);
$I->assertFalse($usage->tariff->isTested());


Number::dao()->actualizeStatus($number);
$number->refresh();

$I->assertEquals($number->status, Number::STATUS_ACTIVE_COMMERCIAL);
$I->assertEquals($number->client_id, $clientAccount->id);
$I->assertEquals($number->usage_id, $usage->id);

//выключаем
$form = new UsageVoipEditForm();
$form->initModel($usage->clientAccount, $usage);
$form->connecting_date = $now->modify("-2 day")->format("Y-m-d");
$form->disconnecting_date = $now->modify("-1 day")->format("Y-m-d");
$form->edit();

$number->refresh();
checkHold($I, $number);

$number->hold_to = $now->modify("-1 minute")->format(DateTime::ATOM);
$I->assertTrue($number->save());

\app\commands\NumberController::actionReleaseFromHold();

$number->refresh();

checkInStock($I, $number);
$I->assertEquals($usage->delete(), 1);

$usage = createUsage($I, $clientAccount, $number, 624 /*тестовый тариф*/);

$I->assertNotNull($usage);
$I->assertNotNull($usage->tariff);
$I->assertNotEmpty($usage->tariff->name);
$I->assertTrue($usage->tariff->isTested());

$number->refresh();

$I->assertEquals($number->status, Number::STATUS_ACTIVE_TESTED);
$I->assertEquals($number->client_id, $clientAccount->id);
$I->assertEquals($number->usage_id, $usage->id);

//выключаем
$form = new UsageVoipEditForm();
$form->initModel($usage->clientAccount, $usage);
$form->connecting_date = $now->modify("-2 day")->format("Y-m-d");
$form->disconnecting_date = $now->modify("-1 day")->format("Y-m-d");
$form->edit();

$number->refresh();

checkInStock($I, $number);
$I->assertEquals($usage->delete(), 1);


$transaction->rollBack();

/**
 * Создание болванки аккаунта
 * @return ClientAccount
 */
function createSingleClientAccount()
{
    $client = new ClientAccount();
    $client->is_active = 0;
    $client->validate();
    $client->save();
    $client->client = 'id' . $client->id;
    $client->timezone_name = \app\helpers\DateTimeZoneHelper::TIMEZONE_DEFAULT;
    $client->currency = \app\models\Currency::RUB;
    $client->save();
    return $client;
}

/**
 * Проверка номера на наличие в продаже
 *
 * @param _FuncTester $I
 * @param Number $number
 */
function checkInStock($I, \app\models\Number $number)
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
function checkHold($I, \app\models\Number $number)
{
    $I->assertEquals($number->status, Number::STATUS_NOTACTIVE_HOLD);
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

function createUsage($I, ClientAccount $clientAccount, \app\models\Number $number, $tarifMainId)
{
    $form = new UsageVoipEditForm();
    $form->scenario = 'add';
    $form->timezone = $clientAccount->timezone_name;
    $form->initModel($clientAccount);
    $form->did = $number->number;
    $form->tariff_main_id = $tarifMainId;
    $form->prepareAdd();

    $I->assertTrue($form->validate());
    $I->assertTrue($form->add());

    $usage = UsageVoip::findOne(['id' => $form->id]);
    return $usage;
}