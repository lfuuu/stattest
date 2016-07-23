<?php

use app\models\filter\FreeNumberFilter;

/**
 * 1. Резервирование номера;
 * 2. Резервирование зарезервированного номера;
 * 3. Проверка наличия зарезервированного номера в списке свободных номеров
 */

$email = 'reserve_free_number_' . mt_rand(0, 100) . '@mcn.ru';

$query = http_build_query([
    'test' => 1,
    'action' => 'add_client',
    'company' => 'Reserve Free Number Test',
    'phone' => '89264290001',
    'email' => $email,
    'client_comment' => 'Reserve Free Number Test',
    'fio' => 'fio',
]);

$I = new _WebTester($scenario);
$I->wantTo('Reserve Free Number Test');
$I->amOnPage('/operator/service.php?' . $query);
$I->dontSee('error:');
$I->see('ok:');

$clientAccountId = app\models\ClientContact::find()->select('client_id')->where(['data' => $email])->scalar();

$freeNumber =
    (new FreeNumberFilter)
        ->getNumbers()
        ->setRegions([99])
        ->randomOne();

$query = http_build_query([
    'test' => 1,
    'action' => 'reserve_number',
    'number' => $freeNumber->number,
    'client_id' => $clientAccountId,
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
$I->see('0');

$query = http_build_query([
    'test' => 1,
    'action' => 'get_free_numbers',
    'region' => 99,
]);

$I = new _WebTester($scenario);
$I->wantTo('Web site integration');
$I->wantTo('Get free numbers after reserve');
$I->amOnPage('/operator/service.php?' . $query);

$value = $I->grabTextFrom('*');
$I->assertNotEmpty($value);

preg_match('#.*?[\r\n]#', $value, $match);
$I->assertNotEmpty($match[0]);

list($number,,,$region) = explode(';', trim($match[0]));
$I->assertNotEmpty($number);
$I->assertEquals(99, trim($region));
$I->dontSee($freeNumber->number);