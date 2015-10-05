<?php 

$I = new _WebTester($scenario);
$I->wantTo('test the integration with the website');
$I->wantTo('get free numbers');
$I->amOnPage("/operator/service.php?action=get_free_numbers&region=99&test=1");
$I->see("74992130007;1;0;99");
$I->see("74992130006;1;0;99");

$I = new _WebTester($scenario);
$I->wantTo("register client from site (without vats)");
$I->amOnPage("/operator/service.php?action=add_client&company=test&phone=89264290001&email=test%40mcn.ru&client_comment=test+TEST&fio=fio&phone_connect=&lk_access=1&vats_tariff_id=&test=1");
$I->dontSee("error:");
$I->see("ok:35801");

$I = new _WebTester($scenario);
$I->wantToTest("re-register client");
$I->amOnPage("/operator/service.php?action=add_client&company=test&phone=89264290001&email=test%40mcn.ru&client_comment=test+TEST&fio=fio&phone_connect=&lk_access=1&vats_tariff_id=&test=1");
$I->dontSee("error:");
$I->see("ok:35801");

$clientId = 35801;
$number = "74992130006";

$I = new _WebTester($scenario);
$I->wantTo("reserv number");
$I->amOnPage("/operator/service.php?action=reserve_number&number=".$number."&client_id=".$clientId."&test=1");
$I->dontSee("0");
$I->see("1");

$I = new _WebTester($scenario);
$I->wantTo("reserv reserved number ");
$I->amOnPage("/operator/service.php?action=reserve_number&number=".$number."&client_id=".$clientId."&test=1");
$I->see("Exception");
$I->see("Номер уже используется");

$I = new _WebTester($scenario);
$I->wantTo('get free numbers after reserv');
$I->amOnPage("/operator/service.php?action=get_free_numbers&region=99&test=1");
$I->see("74992130007;1;0;99");
$I->dontSee("74992130006;1;0;99");
