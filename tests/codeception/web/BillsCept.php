<?php

use tests\codeception\_pages\LoginPage;
use tests\codeception\_pages\BillsPage;

use app\tests\codeception\fixtures\ClientFixture;
use app\tests\codeception\fixtures\ClientContragentFixture;
use app\tests\codeception\fixtures\ClientSuperFixture;
use app\tests\codeception\fixtures\LogClientFixture;
use app\tests\codeception\fixtures\LogClientFieldsFixture;
use app\tests\codeception\fixtures\LogTarifFixture;
use app\tests\codeception\fixtures\LogBlockFixture;
use app\tests\codeception\fixtures\Usage8800Fixture;
use app\tests\codeception\fixtures\UsageExtraFixture;
use app\tests\codeception\fixtures\UsageIpPortsFixture;
use app\tests\codeception\fixtures\UsageSmsFixture;
use app\tests\codeception\fixtures\UsageVirtpbxFixture;
use app\tests\codeception\fixtures\UsageVoipFixture;
use app\tests\codeception\fixtures\UsageWelltimeFixture;
use app\tests\codeception\fixtures\TariffVoipFixture;
use app\tests\codeception\fixtures\Tariff8800Fixture;
use app\tests\codeception\fixtures\TariffExtraFixture;
use app\tests\codeception\fixtures\TariffInternetFixture;
use app\tests\codeception\fixtures\TariffSmsFixture;
use app\tests\codeception\fixtures\TariffVirtpbxFixture;
use app\tests\codeception\fixtures\EmailsFixture;


$I = new _WebTester($scenario);
$I->wantTo('Создать счета');

$tariffVoip = new TariffVoipFixture();
$tariffVoip->load();

$tariff8800 = new Tariff8800Fixture();
$tariff8800->load();

$tariffExtra = new TariffExtraFixture();
$tariffExtra->load();

$tariffInternet = new TariffInternetFixture();
$tariffInternet->load();

$tariffSms = new TariffSmsFixture();
$tariffSms->load();

$tariffVirtpbx = new TariffVirtpbxFixture();
$tariffVirtpbx->load();

$clients = new ClientFixture();
$clients->load();

$clientContragent = new ClientContragentFixture();
$clientContragent->load();

$clientSuper = new ClientSuperFixture();
$clientSuper->load();

$logClient = new LogClientFixture();
$logClient->load();

$logClientFields = new LogClientFieldsFixture();
$logClientFields->load();

$logTarif = new LogTarifFixture();
$logTarif->load();

$logBlock = new LogBlockFixture();
$logBlock->load();

$usage8800 = new Usage8800Fixture();
$usage8800->load();

$usageExtra = new UsageExtraFixture();
$usageExtra->load();

$usageIpPorts = new UsageIpPortsFixture();
$usageIpPorts->load();

$usageSms = new UsageSmsFixture();
$usageSms->load();

$usageVirtpbx = new UsageVirtpbxFixture();
$usageVirtpbx->load();

$usageVoip = new UsageVoipFixture();
$usageVoip->load();

$usageWelltime = new UsageWelltimeFixture();
$usageWelltime->load();

$emails = new EmailsFixture();
$emails->load();



$loginPage = LoginPage::loginAsAdmin($I);

$clients = array(
    'voip' => array(
        'links' => array(
            'Абонентская плата за телефонный номер 74992130216',
            'Тариф АТС'
        )
    ), 
    'welltime' => array(
        'links' => array(
            'Тариф Welltime'
        )
    ), 
    'sms' => array(
        'links' => array(
            'Абонентская плата за СМС рассылки, Тариф СМС'
        )
    ),
    'internet' => array(
        'links' => array(
            'Абонентская плата за доступ в интернет (подключение 7711, тариф Тариф интернет)'
        )
    ),
    's8800' => array(
        'links' => array(
            'Тариф 8800'
        )
    ),
    'collocation' => array(
        'links' => array(
            'Абонентская плата за доступ в интернет (подключение 7712, тариф Тариф Collocation)'
        )
    ),
    'email' => array(
        'links' => array(
            'Поддержка почтового ящика yashchic@mcn.ru'
        )
    ),
    'extra' => array(
        'links' => array(
            'Тариф экстра'
        )
    ),
);

$dates = array(
    '2014-09-10' => ' с 10 по 30 сентября',
    '2014-10-01' => ' с 01 по 31 октября',
    '2014-11-01' => ' с 01 по 20 ноября',
);

foreach ($clients as $client => $data)
{
    $I->amOnPage('/?module=clients&id='.$client);
    foreach ($dates as $date => $period)
    {
        $bill = BillsPage::openBy($I);
        $bill->createRegularBill($date);
        //проверка существования ссылок
        foreach ($data['links'] as $l)
        {
            $link = $l . $period;
            $I->seeLink($link);
        }
    }
}

