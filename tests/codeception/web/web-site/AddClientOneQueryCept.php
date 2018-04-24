<?php

/**
 * Добавление клиента c/без ВАТС с/без номера в росиии/венгрии. По новой схеме, за 1 запрос.
 */


// Берем номера для работы
function getFreeNumber($scenario)
{
    $query = http_build_query([
        'test' => 1,
        'action' => 'get_free_numbers',
        'region' => 99,
    ]);

    $I = new _WebTester($scenario);
    $I->wantTo('Web site integration');
    $I->wantTo('Get free numbers');
    $I->amOnPage('/operator/service.php?' . $query);

    $value = $I->grabTextFrom('*');
    $I->assertNotEmpty($value);

    preg_match('#.*?[\r\n]#', $value, $match);
    $I->assertNotEmpty($match[0]);

    list($number, , , $region) = explode(';', trim($match[0]));
    $I->assertNotEmpty($number);
    $I->assertEquals(99, trim($region));

    return $number;
}

$number = getFreeNumber($scenario);


// rus, no number, empty client
$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89260000001',
    'email' => 'one_query_create_' . mt_rand(0, 1000) . '@mcn.ru',
    'client_comment' => 'test TEST',
    'fio' => 'fio',
    'phone_connect' => '',
    'is_lk_access' => 1,
    'vats_tariff_id' => '',
    'numbers' => '',
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client without number');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$lastAccount = app\models\ClientAccount::find()->select('max(id)')->scalar();
$I->assertNotNull($lastAccount);
$I->see('ok:' . $lastAccount);
$account = \app\models\ClientAccount::findOne(['id' => $lastAccount]);
$I->assertNotNull($account);
$uVoip = \app\models\UsageVoip::findOne(['client' => $account->client]);
$I->assertNull($uVoip);


// rus, number
$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89260000001',
    'email' => 'one_query_create_' . mt_rand(0, 1000) . '@mcn.ru',
    'client_comment' => 'test TEST',
    'fio' => 'fio',
    'phone_connect' => '',
    'is_lk_access' => 1,
    'vats_tariff_id' => '',
    'numbers' => $number,
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client in Russia with number');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$lastAccount = app\models\ClientAccount::find()->select('max(id)')->scalar();
$I->assertNotNull($lastAccount);
$I->see('ok:' . $lastAccount);
$account = \app\models\ClientAccount::findOne(['id' => $lastAccount]);
$I->assertNotNull($account);
$uVoip = \app\models\UsageVoip::findOne(['client' => $account->client]);
$I->assertNotNull($uVoip);
$I->assertEquals($uVoip->E164, $number);


// rus, vpbx, without number
$emailRussia = 'one_query_create_' . mt_rand(0, 1000) . '@mcn.ru';
$ip = rand(1, 255) . "." . rand(1, 255) . "." . rand(1, 255). "." . rand(1,255);
$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89260000001',
    'email' => $emailRussia,
    'client_comment' => 'test TEST',
    'fio' => 'fio',
    'phone_connect' => '',
    'is_lk_access' => 1,
    'vats_tariff_id' => \app\models\TariffVirtpbx::TEST_TARIFF_ID,
    'numbers' => '',
    'ip' => $ip,
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client in Russia without number, with vpbx');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$contact = \app\models\ClientContact::findOne(['type' => 'email', 'data' => $emailRussia]);
$I->assertNotNull($contact);
$account = $contact->client;
$I->assertNotNull($account);
$I->see('ok:' . $account->id);
$uVoip = \app\models\UsageVoip::findOne(['client' => $account->client]); //создается номер для ВАТС
$I->assertNotNull($uVoip);
$I->assertContains('749', (string)$uVoip->E164);
$uVpbx = \app\models\UsageVirtpbx::findOne(['client' => $account->client]);
$I->assertNotNull($uVpbx);



// huf, vpbx, without number
$emailHungary = 'one_query_create_' . mt_rand(0, 1000) . '@mcn.ru';
$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89260000001',
    'email' => $emailHungary,
    'client_comment' => 'test TEST',
    'fio' => 'fio',
    'phone_connect' => '',
    'is_lk_access' => 1,
    'vats_tariff_id' => \app\models\TariffVirtpbx::TEST_TARIFF_ID,
    'numbers' => '',
    'connect_region' => \app\models\Region::HUNGARY
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client in Hungary, without number, with vpbx');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$contact = \app\models\ClientContact::findOne(['type' => 'email', 'data' => $emailHungary]);
$I->assertNotNull($contact);
$account = $contact->client;
$I->assertNotNull($account);
$I->see('ok:' . $account->id);
$uVoip = \app\models\UsageVoip::findOne(['client' => $account->client]); //создается номер для ВАТС
$I->assertNotNull($uVoip);
$I->assertContains('100', (string)$uVoip->E164); // линия без номера. Начинаются с 1000.
$uVpbx = \app\models\UsageVirtpbx::findOne(['client' => $account->client]);
$I->assertNotNull($uVpbx);


// errors

//number already use
$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89260000001',
    'email' => 'one_query_create_' . mt_rand(0, 1000) . '@mcn.ru',
    'client_comment' => 'test TEST',
    'fio' => 'fio',
    'phone_connect' => '',
    'is_lk_access' => 1,
    'vats_tariff_id' => '',
    'numbers' => $number,
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client with error: Number already added');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('ok:');
$I->see('error:' . \app\classes\api\Errors::ERROR_RESERVE_NUMBER_BUSY);


//email already
$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89260000001',
    'email' => $emailRussia,
    'client_comment' => 'test TEST',
    'fio' => 'fio',
    'phone_connect' => '',
    'is_lk_access' => 1,
    'vats_tariff_id' => '',
    'numbers' => '',
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client with error: Email already');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('ok:');
$I->see('error:' . \app\classes\api\Errors::ERROR_EMAIL_ALREADY);


//resend form
$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89260000001',
    'email' => $emailRussia,
    'client_comment' => 'test TEST',
    'fio' => 'fio',
    'phone_connect' => '',
    'is_lk_access' => 1,
    'vats_tariff_id' => '',
    'numbers' => '',
    'ip' => $ip,
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client with error: Resend form without answer');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('ok:');
$I->see('error:' . \app\classes\api\Errors::ERROR_EXECUTE);

//internal error
$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89260000001',
    'email' => '',
    'client_comment' => 'test TEST',
    'fio' => 'fio',
    'phone_connect' => '',
    'is_lk_access' => 1,
    'vats_tariff_id' => '',
    'numbers' => '',
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client with error: internal error');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('ok:');
$I->see('error:' . \app\classes\api\Errors::ERROR_INTERNAL);
