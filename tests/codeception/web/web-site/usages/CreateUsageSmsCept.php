<?php

use tests\codeception\_pages\LoginPage;

/**
 * Тест создания услуги "SMS"
 */

$email = 'usage_sms_' . mt_rand(0, 100) . '@mcn.ru';

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'Create UsageSms Test',
    'phone' => '89264290005',
    'email' => $email,
    'client_comment' => 'Create UsageSms Test',
    'fio' => 'fio',
]);

$I = new _WebTester($scenario);
$I->wantTo('Create UsageSms');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$clientAccountId = app\models\ClientContact::find()->select('client_id')->where(['data' => $email])->scalar();

$I = new _WebTester($scenario);
$I->wantTo('UsageSms form');

$loginPage = LoginPage::loginAsAdmin($I);

$I->amOnPage('/client/view?id=' . $clientAccountId);

$I->amOnPage('/?module=services&action=sms_add');
// Don't see alert about missed client
$I->dontSeeElement('div.alert-danger');

// Trying send form
$tariffSelector = '//select[@id="tarif_id"]';
$tariffText = $I->grabTextFrom($tariffSelector . '/option[last()]');
$I->selectOption($tariffSelector, $tariffText);

$I->fillField('//input[@id="comment"]', 'test comment');

$I->submitForm('//form[@id="dbform"]', []);

// Checking result URL
$I->seeInCurrentUrl('/pop_services.php?table=usage_sms&id=');
$usageId = $I->grabFromCurrentUrl('~id=(\d+)~');

// Checking usage
/** @var \app\models\UsageSms $usage */
$usage = \app\models\UsageSms::findOne($usageId);
$I->assertNotNull($usage, 'UsageID:' . $usageId);
$I->assertNotEmpty($usage->client, 'Client is good');
$I->assertNotEmpty($usage->tarif_id, 'Tariff is good');
$I->assertNotEmpty($usage->activation_dt, 'Activation datetime is good');
$I->assertNotEmpty($usage->expire_dt, 'Expire datetime is good');