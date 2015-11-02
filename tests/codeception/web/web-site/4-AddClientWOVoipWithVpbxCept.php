<?php

/**
 * Добавление клиента с ВАТС без номера, если у клиента нет номеров
 */

$lastAccount = app\models\ClientAccount::find()->select('max(id)')->scalar();
$accountId = $lastAccount + 1;

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'test',
    'phone' => '89264290002',
    'email' => 'test-vpbx@mcn.ru',
    'client_comment' => 'test VPBX',
    'fio' => 'fio',
    'phone_connect' => '',
    'lk_access' => 1,
    'vats_tariff_id' => 42,
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Add client with Vpbx w/o Voip');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:' . $accountId);
$I->dontSee('vpbx:not_found_tariff');
$I->see('vpbx:created');
$I->dontSee('Exception');
$I->dontSee('voip:failed');
$I->see('voip:added');
