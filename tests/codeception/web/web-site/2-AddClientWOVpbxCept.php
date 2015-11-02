<?php

/**
 * 1. Добавление клиента без ВАТС
 * 2. Повторное добавление клиента без ВАТС
 */

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89264290001',
    'email' => 'test@mcn.ru',
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

$lastAccount = app\models\ClientAccount::find()->select('max(id)')->scalar();
$accountId = $lastAccount;

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add duplicate client without Vpbx');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:' . $accountId);