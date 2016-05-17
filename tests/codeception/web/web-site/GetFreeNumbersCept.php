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

$value = $I->grabTextFrom('*');
$I->assertNotEmpty($value);

preg_match('#.*?[\r\n]#', $value, $match);
$I->assertNotEmpty($match[0]);

list($number,,,$region) = explode(';', trim($match[0]));
$I->assertNotEmpty($number);
$I->assertEquals(99, trim($region));