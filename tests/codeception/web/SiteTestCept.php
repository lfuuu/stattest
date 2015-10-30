<?php 

use app\models\ClientAccount;

$lastAccount = ClientAccount::find()->select('max(id)')->scalar();
$accountId = $lastAccount+1;

$number = "74992130006";

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
$I->see("ok:".$accountId);

$I = new _WebTester($scenario);
$I->wantToTest("re-register client");
$I->amOnPage("/operator/service.php?action=add_client&company=test&phone=89264290001&email=test%40mcn.ru&client_comment=test+TEST&fio=fio&phone_connect=&lk_access=1&vats_tariff_id=&test=1");
$I->dontSee("error:");
$I->see("ok:".$accountId);

$account = ClientAccount::findOne(['id' => $accountId]);
$I->assertNotNull($account);
$I->assertEquals($account->credit, ClientAccount::DEFAULT_CREDIT);
$I->assertEquals($account->voip_is_day_calc, ClientAccount::DEFAULT_VOIP_IS_DAY_CALC);
$I->assertEquals($account->voip_credit_limit_day, ClientAccount::DEFAULT_VOIP_CREDIT_LIMIT_DAY);


$I = new _WebTester($scenario);
$I->wantTo("reserv number");
$I->amOnPage("/operator/service.php?action=reserve_number&number=".$number."&client_id=".$accountId."&test=1");
$I->dontSee("0");
$I->see("1");

$I = new _WebTester($scenario);
$I->wantTo("reserv reserved number ");
$I->amOnPage("/operator/service.php?action=reserve_number&number=".$number."&client_id=".$accountId."&test=1");
$I->see("Exception");
$I->see("Номер уже используется");

$I = new _WebTester($scenario);
$I->wantTo('get free numbers after reserv');
$I->amOnPage("/operator/service.php?action=get_free_numbers&region=99&test=1");
$I->see("74992130007;1;0;99");
$I->dontSee("74992130006;1;0;99");
