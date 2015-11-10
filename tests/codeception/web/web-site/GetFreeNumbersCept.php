<?php

/**
 * Выбор свободного номера
 */

$query = http_build_query([
    'test' => 1,
    'action' => 'get_free_numbers',
    'region' => 99,
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Get free numbers');
$I->amOnPage('/operator/service.php?' . $query);
$I->see('74992130007;1;0;99');
$I->see('74992130006;1;0;99');