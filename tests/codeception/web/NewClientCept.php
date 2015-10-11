<?php 


use tests\codeception\_pages\NewClientPage;
use tests\codeception\_pages\LoginPage;

$I = new _WebTester($scenario);
$I->wantTo('create new LC');

$loginPage = LoginPage::loginAsAdmin($I);
$I->seeLink("Новый клиент");

$cl = NewClientPage::openBy($I);

$data = [
    'ContragentEditForm' => [
        'country_id' => 643,
        'legal_type' => 'legal',
        'super_id' => '',
        'name' => 'Ромашка',
        'address_jur' => 'Южное поле. д.1',
        'name_full' => 'ООО "Ромашка"',
        'inn' => '7707049388',
        'kpp' => '',
        'okvd' => '',
        'ogrn' => '',
        'opf_id' => '0',
        'okpo' => '',
        'tax_regime' => 'undefined',
        'position' => '',
        'fio' => '',
        'comment' => 'тест'
    ],

    'ContractEditForm' => [
        'business_id' => 2,
        'business_process_id' => '1',
        'manager' => '',
        'business_process_status_id' => '19',
        'account_manager' => '',
        'organization_id' => '1',
        'state' => 'unchecked'
    ],

    'AccountEditForm' => [
        'admin_email' => 'fagob6@inboxstore.me',
        'region' => '99',
        'timezone_name' => 'Europe/Moscow',
        'sale_channel' => '',
        'nal' => 'beznal',
        'currency' => 'RUB',
        'price_type' => '739a53ba-8389-11df-9af5-001517456eb1',
        'credit' => '0',
        'credit' => '1',
        'credit_size' => '0',
        'voip_credit_limit' => '',
        'voip_credit_limit_day' => '1000',
        'voip_is_day_calc' => '0',
        'voip_is_day_calc' => '1',
        'mail_print' => '0',
        'is_with_consignee' => '0',
        'address_post' => '',
        'head_company' => '',
        'address_post_real' => '',
        'head_company_address_jur' => '',
        'mail_who' => '',
        'consignee' => '',
        'form_type' => 'manual',
        'stamp' => '0',
        'is_upd_without_sign' => '0',
        'is_agent' => '0',
        'bill_rename1' => '',
        'bill_rename1' => 'no',
        'bik' => '',
        //'corr_acc' => '',
        'pay_acc' => '',
        //'bank_name' => '',
        //'bank_city' => ''
    ]
];


$cl->createClient($data);
$I->seeInCurrentUrl("view");
$I->see("fagob6@inboxstore.me");
//$I->see("Заказ услуг");
$I->seeLink("Договор № 35800");
