<?php

use tests\codeception\_pages\LoginPage;

$I = new _FunctionalTester($scenario);
$I->wantTo('проверить что работает логин');

$loginPage = LoginPage::openBy($I);

$I->see('Введите логин и пароль');

$I->amGoingTo('пробую войти без логина и пароля');
$loginPage->login('', '');
$I->expectTo('увидеть ошибки валидации');
$I->see('Необходимо заполнить «Username».');
$I->see('Необходимо заполнить «Password».');

$I->amGoingTo('пробую войти с неправильным паролем');
$loginPage->login('admin', 'wrong');
$I->expectTo('увидеть ошибки валидации');
$I->see('Не правильный логин или пароль');

$I->amGoingTo('пробую войти с правильными логином и паролем');
$loginPage->login('admin', 'admin');
$I->expectTo('увидеть ссылку на выход');
$I->see('Logout');
