<?php

/**
 * 1. Добавление клиента без ВАТС
 * 2. Повторное добавление клиента без ВАТС
 */

$email = mt_rand(0, 100) . '@mcn.ru';

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89264290001',
    'email' => $email,
    'client_comment' => 'test TEST',
    'fio' => 'fio',
    'phone_connect' => '',
    'lk_access' => 1,
    'vats_tariff_id' => '',
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client without Vpbx');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$clientAccountId = app\models\ClientContact::find()->select('client_id')->where(['data' => $email])->scalar();

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add duplicate client without Vpbx');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:' . $clientAccountId);