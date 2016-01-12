<?php

use tests\codeception\_pages\LoginPage;

/**
 * Тест создания услуги "ВАТС"
 */

$email = 'usage_virtpbx_' . mt_rand(0, 100) . '@mcn.ru';

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'Create UsageVirtPbx Test',
    'phone' => '89264290004',
    'email' => $email,
    'client_comment' => 'Create UsageVirtPbx Test',
    'fio' => 'fio',
]);

$I = new _WebTester($scenario);
$I->wantTo('Create UsageVirtPbx');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$clientAccountId = app\models\ClientContact::find()->select('client_id')->where(['data' => $email])->scalar();

$I = new _WebTester($scenario);
$I->wantTo('UsageVirtPbx form');

$loginPage = LoginPage::loginAsAdmin($I);

$I->amOnPage('/client/view?id=' . $clientAccountId);

$I->amOnPage('/?module=services&action=virtpbx_add');
// Don't see alert about missed client
$I->dontSeeElement('div.alert-danger');

// Trying send form
$regionSelector = '//select[@id="region"]';
$regionText = $I->grabTextFrom($regionSelector . '/option[last()]');
$I->selectOption($regionSelector, $regionText);

$I->submitForm('//form[@id="dbform"]', []);

// Checking result URL
$I->seeInCurrentUrl('/pop_services.php?table=usage_virtpbx&id=');
$usageId = $I->grabFromCurrentUrl('~id=(\d+)~');

// Checking usage
/** @var \app\models\UsageIpPorts $usage */
$usage = \app\models\UsageIpPorts::findOne($usageId);
$I->assertNotNull($usage, 'UsageID:' . $usageId);
$I->assertNotEmpty($usage->client, 'Client is good');
$I->assertNotEmpty($usage->activation_dt, 'Activation datetime is good');
$I->assertNotEmpty($usage->expire_dt, 'Expire datetime is good');