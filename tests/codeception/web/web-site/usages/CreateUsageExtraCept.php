<?php

use tests\codeception\_pages\LoginPage;

/**
 * Тест создания услуги "Доп. услуги"
 */

$email = 'usage_extra_' . mt_rand(0, 100) . '@mcn.ru';

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'Create UsageExtra Test',
    'phone' => '89264290001',
    'email' => $email,
    'client_comment' => 'Create UsageExtra Test',
    'fio' => 'fio',
]);

$I = new _WebTester($scenario);
$I->wantTo('Create UsageExtra');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$clientAccountId = app\models\ClientContact::find()->select('client_id')->where(['data' => $email])->scalar();

$I = new _WebTester($scenario);
$I->wantTo('UsageExtra form');

$loginPage = LoginPage::loginAsAdmin($I);

$I->amOnPage('/client/view?id=' . $clientAccountId);

$I->amOnPage('/?module=services&action=ex_add');
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
$codeSelector = '//select[@id="code"]';
$codeText = $I->grabAttributeFrom($codeSelector . '/option[last()]', 'value');
$I->selectOption($codeSelector, $codeText);

$tariffSelector = '//select[@id="tarif_id"]';
$tariffId = $I->grabAttributeFrom($tariffSelector . '/option[last()]', 'value');
$I->selectOption($tariffSelector, $tariffId);

$I->fillField('//input[@id="comment"]', 'test comment');

$I->submitForm('//form[@id="dbform"]', []);

// Checking result URL
$I->seeInCurrentUrl('/?module=services&action=ex_view');
$I->seeElement('div.alert-success');
$usageId = $I->grabTextFrom('~<a href=".*usage_extra&id=(\d+)[^>]+>~');

// Checking usage
/** @var \app\models\UsageExtra $usage */
$usage = \app\models\UsageExtra::findOne($usageId);
$I->assertNotNull($usage, 'UsageID:' . $usageId);
$I->assertNotNull($usage->clientAccount, 'Client #' . $clientAccountId . ' is good');
$I->assertNotEmpty($usage->amount, 'Amount is good');
$I->assertNotEmpty($usage->status, 'Status is good');
$I->assertNotEmpty($usage->activation_dt, 'Activation datetime is good');
$I->assertNotEmpty($usage->expire_dt, 'Expire datetime is good');
$I->assertNotNull($usage->tariff, 'Tariff is good');
