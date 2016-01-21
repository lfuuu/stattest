<?php

use tests\codeception\_pages\LoginPage;

/**
 * Тест создания услуги "Welltime"
 */

$email = 'usage_welltime_' . mt_rand(0, 100) . '@mcn.ru';

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'Create UsageWelltime Test',
    'phone' => '89264290002',
    'email' => $email,
    'client_comment' => 'Create UsageWelltime Test',
    'fio' => 'fio',
]);

$I = new _WebTester($scenario);
$I->wantTo('Create UsageWelltime');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$clientAccountId = app\models\ClientContact::find()->select('client_id')->where(['data' => $email])->scalar();

$I = new _WebTester($scenario);
$I->wantTo('UsageWelltime form');

$loginPage = LoginPage::loginAsAdmin($I);

$I->amOnPage('/client/view?id=' . $clientAccountId);

$I->amOnPage('/?module=services&action=welltime_add');
// Don't see alert about missed client
$I->dontSeeElement('div.alert-danger');


/*
 *  Negative test
 */
$I->submitForm('//form[@id="dbform"]', []);
$I->seeElement('div.alert-danger');

/*
 * Positive test
 */
$tariffSelector = '//select[@id="tarif_id"]';
$tariffValue = $I->grabAttributeFrom($tariffSelector . '/option[last()]', 'value');
$I->selectOption($tariffSelector, $tariffValue);

$I->fillField('//input[@id="ip"]', '127.0.0.11');

$routerSelector = '//select[@id="router"]';
$routerText = $I->grabTextFrom($routerSelector . '/option[last()]');
$I->selectOption($routerSelector, $routerText);

$I->fillField('//input[@id="amount"]', 2);
$I->fillField('//input[@id="comment"]', 'test comment');

$I->submitForm('//form[@id="dbform"]', []);

// Checking result URL
$I->seeInCurrentUrl('/pop_services.php?table=usage_welltime&id=');
$usageId = $I->grabFromCurrentUrl('~id=(\d+)~');

// Checking usage
/** @var \app\models\UsageWelltime $usage */
$usage = \app\models\UsageWelltime::findOne($usageId);
$I->assertNotNull($usage, 'UsageID:' . $usageId);
$I->assertNotNull($usage->clientAccount, 'Client is good');
$I->assertNotEmpty($usage->amount, 'Amount is good');
$I->assertNotEmpty($usage->status, 'Status is good');
$I->assertNotEmpty($usage->activation_dt, 'Activation datetime is good');
$I->assertNotEmpty($usage->expire_dt, 'Expire datetime is good');
$I->assertNotNull($usage->tariff, 'Tariff is good');
$I->assertNotEmpty($usage->router, 'Router is good');