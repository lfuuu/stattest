<?php

use tests\codeception\_pages\LoginPage;

/**
 * Тест создания услуги "Emails"
 */

$email = 'usage_emails_' . mt_rand(0, 100) . '@mcn.ru';

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'Create UsageEmails Test',
    'phone' => '89264290003',
    'email' => $email,
    'client_comment' => 'Create UsageEmails Test',
    'fio' => 'fio',
]);

$I = new _WebTester($scenario);
$I->wantTo('Create UsageEmails');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$clientAccountId = app\models\ClientContact::find()->select('client_id')->where(['data' => $email])->scalar();

$I = new _WebTester($scenario);
$I->wantTo('UsageEmails form');

$loginPage = LoginPage::loginAsAdmin($I);

$I->amOnPage('/client/view?id=' . $clientAccountId);

$I->amOnPage('/?module=services&action=em_add');
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
$domainSelector = '//select[@id="domain"]';
$domainText = $I->grabTextFrom($domainSelector . '/option[last()]');
$I->selectOption($domainSelector, $domainText);

$I->fillField('//input[@id="local_part"]', 'usage_tests');
$I->fillField('//input[@id="password"]', '1q2w3e4r5t6y');

$quotaSelector = '//select[@id="box_quota"]';
$domainText = $I->grabTextFrom($quotaSelector . '/option[last()]');
$I->selectOption($quotaSelector, $domainText);

$I->submitForm('//form[@id="dbform"]', []);

// Checking result URL
$I->seeInCurrentUrl('/pop_services.php?table=emails&id=');
$usageId = $I->grabFromCurrentUrl('~id=(\d+)~');

// Checking usage
/** @var \app\models\UsageEmails $usage */
$usage = \app\models\UsageEmails::findOne($usageId);
$I->assertNotNull($usage, 'UsageID:' . $usageId);
$I->assertNotNull($usage->clientAccount, 'Client #' . $clientAccountId . ' is good');
$I->assertNotEmpty($usage->local_part, 'Email account is good');
$I->assertNotEmpty($usage->domain, 'Email domain is good');
$I->assertNotEmpty($usage->actual_from, 'Activation date is good');
$I->assertNotEmpty($usage->actual_to, 'Expire date is good');