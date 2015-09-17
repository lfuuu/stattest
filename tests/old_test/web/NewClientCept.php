<?php

use tests\codeception\_pages\LoginPage;
use tests\codeception\_pages\NewClient;

$I = new _WebTester($scenario);
$I->wantTo('Создать клиента');

$loginPage = LoginPage::loginAsAdmin($I);

$I->expectTo('увидеть ссылку на "клиенты"');
$I->seeLink('Все клиенты');
$I->seeLink('Новый клиент');

$newClientPage = NewClient::openBy($I);
$I->seeCurrentUrlEquals('/index.php?module=clients&action=new');
$I->see('Регион');

$client_data = array(
    'client' => 'my_company',
    'company' => 'first company',
    'inn' => '111111111',
    'kpp' => '1111111111'
);
$newClientPage->createClient($client_data);

$I->amOnPage('/?module=clients&id=my_company');

$I->see('first company');
$I->seeLink('снять');
$I->seeLink('my_company');

$I->seeLink('Создать задание');
$I->click('Создать задание');

$I->see('Добавить заявку (спрятать)');
$I->see('Текст проблемы');
$I->see('Завести заявку');

$I->fillField('textarea[name="problem"]', 'Это моя первая заявка. Не судите строго');
$I->click('Завести заявку');

$I->see('Трабл создал');
$I->see('Это моя первая заявка. Не судите строго');
$I->see('введите отрицательное число, чтобы отнять время');

$I->seeOptionIsSelected('select[name="state"]', 'Открыт');

$I->selectOption('select[name="state"]', 'Выполнен');
$I->fillField('textarea[name="comment"]', 'Вроде как готово');
$I->selectOption('select[name="trouble_rating"]', '2');
$I->click('#submit');

$I->seeOptionIsSelected('select[name="state"]', 'Выполнен');
$I->see('Вроде как готово');

$I->selectOption('select[name="state"]', 'Открыт');
$I->fillField('textarea[name="comment"]', 'Не, не готово');
$I->click('#submit');

$I->seeOptionIsSelected('select[name="state"]', 'Открыт');
$I->see('Не, не готово');

$I->selectOption('select[name="state"]', 'Закрыт');
$I->fillField('textarea[name="comment"]', 'Теперь точно готово');
$I->selectOption('select[name="trouble_rating"]', '4');
$I->click('#submit');

$I->seeOptionIsSelected('select[name="state"]', 'Закрыт');
$I->see('Теперь точно готово');






