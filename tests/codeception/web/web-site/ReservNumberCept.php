<?php

/**
 * 1. Резервирование номера;
 * 2. Резервирование зарезервированного номера;
 * 3. Проверка наличия зарезервированного номера в списке свободных номеров
 */

$lastAccount = app\models\ClientAccount::find()->select('max(id)')->scalar();
$accountId = $lastAccount;

$number = '74992130006';

$query = http_build_query([
    'test' => 1,
    'action' => 'reserve_number',
    'number' => $number,
    'client_id' => $accountId,
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Reserve number');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('0');
$I->see('1');

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Reserve reserved number ');
$I->amOnPage('/operator/service.php?' . $query);
$I->see('Exception');
$I->see('Номер уже используется');

$query = http_build_query([
    'test' => 1,
    'action' => 'get_free_numbers',
    'region' => 99,
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Get free numbers after reserve');
$I->amOnPage('/operator/service.php?' . $query);
$I->see('74992130007;1;0;99');
$I->dontSee($number . ';1;0;99');