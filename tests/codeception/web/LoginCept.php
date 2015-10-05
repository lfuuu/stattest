<?php 

use tests\codeception\_pages\LoginPage;

$I = new _WebTester($scenario);
$I->wantTo('Test login');

$loginPage = LoginPage::openBy($I);

$I->see('Введите логин и пароль');

$I->amGoingTo('пробую войти без логина и пароля');
$loginPage->login('', '');
$I->expectTo('увидеть ошибки валидации');
$I->see('Необходимо заполнить «Логин».');
$I->see('Необходимо заполнить «Пароль».');

$I->amGoingTo('пробую войти с неправильным паролем');
$loginPage->login('admin', 'wrong');
$I->expectTo('увидеть ошибки валидации');
$I->see('Не правильный логин или пароль');

$I->amGoingTo('пробую войти с правильными логином и паролем');
//$loginPage->login('admin', '111');
$loginPage->login('admin', '111');
$I->expectTo('увидеть ссылку на выход');
$I->dontSee('Не правильный логин или пароль');
$I->see('Выход');
