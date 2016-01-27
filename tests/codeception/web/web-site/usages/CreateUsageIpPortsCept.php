<?php

use tests\codeception\_pages\LoginPage;

/**
 * Тест создания услуги "Интернет"
 */

$email = 'usage_ip_ports_' . mt_rand(0, 100) . '@mcn.ru';

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'Create UsageIpPorts Test',
    'phone' => '89264290004',
    'email' => $email,
    'client_comment' => 'Create UsageIpPorts Test',
    'fio' => 'fio',
]);

$I = new _WebTester($scenario);
$I->wantTo('Create UsageIpPorts');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$clientAccountId = app\models\ClientContact::find()->select('client_id')->where(['data' => $email])->scalar();

$I = new _WebTester($scenario);
$I->wantTo('UsageIpPorts form');

$loginPage = LoginPage::loginAsAdmin($I);

$I->amOnPage('/client/view?id=' . $clientAccountId);

$I->amOnPage('/?module=services&action=in_add');
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
$portTypeSelector = '//select[@id="port_type"]';
$portTypeText = $I->grabTextFrom($portTypeSelector . '/option[@value="adsl"]');
$I->selectOption($portTypeSelector, $portTypeText);

$I->fillField('//input[@id="address"]', 'test address');

$I->submitForm('//form[@id="dbform"]', []);

// Checking result URL
$I->seeInCurrentUrl('/pop_services.php?table=usage_ip_ports&id=');
$usageId = $I->grabFromCurrentUrl('~id=(\d+)~');

// Checking usage
/** @var \app\models\UsageIpPorts $usage */
$usage = \app\models\UsageIpPorts::findOne($usageId);
$I->assertNotNull($usage, 'UsageID:' . $usageId);
$I->assertNotNull($usage->clientAccount, 'Client #' . $clientAccountId . ' is good');
$I->assertNotEmpty($usage->amount, 'Amount is good');
$I->assertNotEmpty($usage->port_id, 'Port is good');
$I->assertNotEmpty($usage->activation_dt, 'Activation datetime is good');
$I->assertNotEmpty($usage->expire_dt, 'Expire datetime is good');