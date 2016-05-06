<?php

return;
use tests\codeception\_pages\LoginPage;

/**
 * Тест создания услуги "Интернет" -> "Сеть"
 */

$email = 'usage_ip_routes_' . mt_rand(0, 100) . '@mcn.ru';

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'Create UsageIpRoutes Test',
    'phone' => '89264290004',
    'email' => $email,
    'client_comment' => 'Create UsageIpRoutes Test',
    'fio' => 'fio',
]);

$I = new _WebTester($scenario);
$I->wantTo('Create UsageIpRoutes');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$clientAccountId = app\models\ClientContact::find()->select('client_id')->where(['data' => $email])->scalar();

$I = new _WebTester($scenario);
$I->wantTo('UsageIpRoutes form');

$loginPage = LoginPage::loginAsAdmin($I);

$I->amOnPage('/client/view?id=' . $clientAccountId);

$I->amOnPage('/?module=services&action=in_add');
// Don't see alert about missed client
$I->dontSeeElement('div.alert-danger');

/*
 * Positive IpPorts test
 */
$portTypeSelector = '//select[@id="port_type"]';
$portTypeText = $I->grabTextFrom($portTypeSelector . '/option[@value="adsl"]');
$I->selectOption($portTypeSelector, $portTypeText);

$I->fillField('//input[@id="address"]', 'test address');

$I->submitForm('//form[@id="dbform"]', []);

// Checking result URL
$I->seeInCurrentUrl('/?module=services&action=in_view');
$usageId = $I->grabTextFrom('~<a href=\".*usage_ip_ports&id=(\d+)~');

// Checking usage
/** @var \app\models\UsageIpPorts $usage */
$usage = \app\models\UsageIpPorts::findOne($usageId);
$I->assertNotNull($usage, 'UsageID:' . $usageId);
$I->assertNotNull($usage->clientAccount, 'Client #' . $clientAccountId . ' is good');
$I->assertNotEmpty($usage->amount, 'Amount is good');
$I->assertNotEmpty($usage->port_id, 'Port is good');
$I->assertNotEmpty($usage->activation_dt, 'Activation datetime is good');
$I->assertNotEmpty($usage->expire_dt, 'Expire datetime is good');

$I->amOnPage('/?module=services&action=in_add2&id=' . $usageId);
// See hidden parent element
$I->seeElement('//input[@name="dbform[port_id]"][@value=' . $usageId . ']');

$netSelector = '//select[@id="getnet_size"]';
$netText = $I->grabAttributeFrom($netSelector . '/option[last()]', 'value');
/*
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('/', [
    'test' => 1,
    'module' => 'routers',
    'action' => 'n_acquire_as',
    'query' => $netText
]);

$I->seeResponseIsJSON();
$netAddress = $I->grabDataFromJsonResponse();
$I->assertNotEmpty($netAddress['data'], 'Net address is good');
*/
$netAddress = ['data' => '89.235.159.0/30'];

/*
 * Positive test
 */
$I->fillField('//input[@id="net"]', $netAddress['data']);
$I->fillField('//input[@id="comment"]', 'test comment');

$I->submitForm('//form[@id="dbform"]', []);

// Checking result URL
$I->seeInCurrentUrl('?module=services&action=in_view');
$I->dontSeeElement('div.alert-danger', $netAddress);
$I->seeElement('div.alert-success');
