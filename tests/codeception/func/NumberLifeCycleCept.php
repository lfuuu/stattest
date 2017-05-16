<?php

use app\forms\usage\UsageVoipEditForm;
use app\helpers\DateTimeZoneHelper;
use app\models\filter\FreeNumberFilter;
use app\models\Number;
use app\modules\nnp\models\NdcType;
use tests\codeception\func\_NumberCycleHelper;
use tests\codeception\unit\models\_ClientAccount;


$I = new _FuncTester($scenario);
$I->wantTo('Test Number life cycle');

$helper = new _NumberCycleHelper($I);

$now = new \DateTime('now', new \DateTimeZone(\app\helpers\DateTimeZoneHelper::TIMEZONE_MOSCOW));

$transaction = Yii::$app->db->beginTransaction();

// создаем ЛС
$clientAccount = _ClientAccount::createOne();
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
$helper->checkInStock($number);

// Размещение номера в отстойнике
Number::dao()->startHold($number);
$freeNumber->refresh();
$helper->checkHold($number);

// Отмена размещения номера в отстойнике
Number::dao()->stopHold($number);
$number->refresh();
$helper->checkInStock($number);

// Снятие номера с продажи
Number::dao()->startNotSell($number);
$number->refresh();
$I->assertEquals($number->status, Number::STATUS_NOTSALE);
$I->assertEquals($number->client_id, 764);

// Отмена снятия номера с продажи
Number::dao()->stopNotSell($number);
$number->refresh();
$helper->checkInStock($number);

// Работа с услугой

// Проверка существования услуги с номером

$usage = $helper->createUsage($clientAccount, $number, 531 /*обычный публичный тариф */);

$I->assertNotNull($usage);
$I->assertNotNull($usage->tariff);
$I->assertNotEmpty($usage->tariff->name);
$I->assertFalse($usage->tariff->isTest());


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
$helper->checkHold($number);

$number->hold_to = $now->modify("-1 minute")->format(DateTimeZoneHelper::DATETIME_FORMAT);
$I->assertTrue($number->save());

\app\commands\NumberController::actionReleaseFromHold();

$number->refresh();

$helper->checkInStock($number);
$I->assertEquals($usage->delete(), 1);

$usage = $helper->createUsage($clientAccount, $number, 624 /*тестовый тариф*/);

$I->assertNotNull($usage);
$I->assertNotNull($usage->tariff);
$I->assertNotEmpty($usage->tariff->name);
$I->assertTrue($usage->tariff->isTest());

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

$helper->checkInStock($number);
$I->assertEquals($usage->delete(), 1);


$transaction->rollBack();

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// portable number

$transaction = Yii::$app->db->beginTransaction();
// создаем ЛС
$clientAccountId = _ClientAccount::createOne();
$I->assertNotNull($clientAccountId);
$clientAccount = \app\models\ClientAccount::findOne(['id' => $clientAccountId]);
$I->assertNotNull($clientAccount);

$ndc = '495';
$testNumber = '74954117356';

$registry = new \app\models\voip\Registry;
$registry->country_id = \app\models\Country::RUSSIA;
$registry->city_id = \app\models\City::DEFAULT_USER_CITY_ID;
$registry->source = \app\classes\enum\VoipRegistrySourceEnum::PORTABILITY;
$registry->ndc_type_id = NdcType::ID_GEOGRAPHIC;
$registry->ndc = $ndc;
$registry->number_from = $testNumber;
$registry->number_to = $testNumber;
$registry->number_full_from = $testNumber;
$registry->number_full_to = $testNumber;
$registry->account_id = $clientAccount->id;
$registry->comment = 'Test registry';

$I->assertTrue($registry->validate());
$I->assertTrue($registry->save());

$registry->fillNumbers();

$number = Number::findOne(['number' => $testNumber]);
$I->assertNotNull($number);
$I->assertEquals($number->is_ported, 1);
$I->assertEquals($number->status, Number::STATUS_NOTACTIVE_RESERVED);

$usage = $helper->createUsage($clientAccount, $number, 531 /*обычный публичный тариф */);

$I->assertNotNull($usage);
$I->assertNotNull($usage->tariff);

$number->refresh();

$I->assertEquals($number->status, Number::STATUS_ACTIVE_COMMERCIAL);
$I->assertEquals($number->client_id, $clientAccount->id);
$I->assertEquals($number->usage_id, $usage->id);


//выключаем услугу
$form = new UsageVoipEditForm();
$form->initModel($usage->clientAccount, $usage);
$form->connecting_date = $now->modify("-2 day")->format("Y-m-d");
$form->disconnecting_date = $now->modify("-1 day")->format("Y-m-d");
$form->edit();

$number->refresh();

$I->assertEquals($number->status, Number::STATUS_RELEASED);
$I->assertEquals($number->client_id, null);
$I->assertEquals($number->usage_id, null);

$I->assertEquals($usage->delete(), 1);
$transaction->rollBack();