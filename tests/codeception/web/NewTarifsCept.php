<?php

use tests\codeception\_pages\LoginPage;

$I = new _WebTester($scenario);
$I->wantTo('Создать тарифы');

$loginPage = LoginPage::loginAsAdmin($I);

$I->expectTo('увидеть ссылку на "Тарифы"');
$I->seeLink('Тарифы');
$I->click('Тарифы');

$tariffs = array(
    'voip' => array(
        'page' => '?module=tarifs&action=voip',
        'add_link' => 'Добавить тариф',
        'selectbox' => array(
            'select[name="region"]' => '99. Москва',
            'select[name="dest"]' => 'Местные Мобильные',
        ), 
        'input' => array(
            'input[name="name"]' => 'test_voip',
            'input[name="name_short"]' => 'short_voip',
            'input[name="month_number"]' => '400',
            'input[name="month_line"]' => '100',
        ),
        'checkbox' => array(
            'input[name="tariffication_by_minutes"]',
            'input[name="tariffication_free_first_seconds"]'
        ),
        'verifications' => array(
            'link' => 'test_voip',
            'text' => 'short_voip',
        ),
        'button' => 'Сохранить'
    ),
    'voip2' => array(
        'page' => '?module=tarifs&action=voip',
        'add_link' => 'Добавить тариф',
        'selectbox' => array(
            'select[name="region"]' => '99. Москва',
        ), 
        'input' => array(
            'input[name="name"]' => 'test_voip2',
            'input[name="name_short"]' => 'short_voip2',
            'input[name="month_number"]' => '400',
            'input[name="month_line"]' => '100',
        ),
        'checkbox' => array(
            'input[name="tariffication_by_minutes"]',
            'input[name="tariffication_free_first_seconds"]'
        ),
        'verifications' => array(
            'link' => 'test_voip2',
            'text' => 'short_voip2',
        ),
        'button' => 'Сохранить'
    ),
    'voip3' => array(
        'page' => '?module=tarifs&action=voip',
        'add_link' => 'Добавить тариф',
        'selectbox' => array(
            'select[name="region"]' => '99. Москва',
            'select[name="dest"]' => 'Россия',
        ), 
        'input' => array(
            'input[name="name"]' => 'test_voip3',
            'input[name="name_short"]' => 'short_voip3',
            'input[name="month_number"]' => '400',
            'input[name="month_line"]' => '100',
        ),
        'checkbox' => array(
            'input[name="tariffication_by_minutes"]',
            'input[name="tariffication_free_first_seconds"]'
        ),
        'verifications' => array(
            'link' => 'test_voip3',
            'text' => 'short_voip3',
        ),
        'button' => 'Сохранить'
    ),
    'voip4' => array(
        'page' => '?module=tarifs&action=voip',
        'add_link' => 'Добавить тариф',
        'selectbox' => array(
            'select[name="region"]' => '99. Москва',
            'select[name="dest"]' => 'СНГ',
        ), 
        'input' => array(
            'input[name="name"]' => 'test_voip4',
            'input[name="name_short"]' => 'short_voip4',
            'input[name="month_number"]' => '400',
            'input[name="month_line"]' => '100',
        ),
        'checkbox' => array(
            'input[name="tariffication_by_minutes"]',
            'input[name="tariffication_free_first_seconds"]'
        ),
        'verifications' => array(
            'link' => 'test_voip4',
            'text' => 'short_voip4',
        ),
        'button' => 'Сохранить'
    ),
    'voip5' => array(
        'page' => '?module=tarifs&action=voip',
        'add_link' => 'Добавить тариф',
        'selectbox' => array(
            'select[name="region"]' => '99. Москва',
            'select[name="dest"]' => 'Международка',
        ), 
        'input' => array(
            'input[name="name"]' => 'test_voip5',
            'input[name="name_short"]' => 'short_voip5',
            'input[name="month_number"]' => '400',
            'input[name="month_line"]' => '100',
        ),
        'checkbox' => array(
            'input[name="tariffication_by_minutes"]',
            'input[name="tariffication_free_first_seconds"]'
        ),
        'verifications' => array(
            'link' => 'test_voip5',
            'text' => 'short_voip5',
        ),
        'button' => 'Сохранить'
    ),
    'internet' => array(
        'page' => '?module=tarifs&action=view&m=internet',
        'add_link' => 'Добавить',
        'selectbox' => array(
            'select[name="dbform[adsl_speed]"]' => '768/10000',
        ), 
        'input' => array(
            'input[name="dbform[name]"]' => 'test_internet',
            'input[name="dbform[pay_once]"]' => '300',
            'input[name="dbform[pay_month]"]' => '450',
            'input[name="dbform[mb_month]"]' => '100',
            'input[name="dbform[pay_mb]"]' => '10',
            'input[name="dbform[comment]"]' => 'Тестовый тариф Интернет',
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'collocation' => array(
        'page' => '?module=tarifs&action=view&m=collocation',
        'add_link' => 'Добавить',
        'input' => array(
            'input[name="dbform[name]"]' => 'test_collocation',
            'input[name="dbform[pay_once]"]' => '300',
            'input[name="dbform[pay_month]"]' => '450',
            'input[name="dbform[month_r]"]' => '80',
            'input[name="dbform[month_r2]"]' => '10',
            'input[name="dbform[month_f]"]' => '10',
            'input[name="dbform[pay_r]"]' => '100',
            'input[name="dbform[pay_r2]"]' => '200',
            'input[name="dbform[pay_f]"]' => '300',
            'input[name="dbform[comment]"]' => 'Тестовый тариф Collocation',
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'vpn' => array(
        'page' => '?module=tarifs&action=view&m=vpn',
        'add_link' => 'Добавить',
        'selectbox' => array(
            'select[name="dbform[adsl_speed]"]' => '768/10000',
            'select[name="dbform[type]"]' => 'VPN (V)',
        ), 
        'input' => array(
            'input[name="dbform[name]"]' => 'test_vpn',
            'input[name="dbform[pay_once]"]' => '300',
            'input[name="dbform[pay_month]"]' => '450',
            'input[name="dbform[mb_month]"]' => '100',
            'input[name="dbform[pay_mb]"]' => '10',
            'input[name="dbform[comment]"]' => 'Тестовый тариф VPN',
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'extra' => array(
        'page' => '?module=tarifs&action=view&m=extra',
        'add_link' => 'Добавить',
        'selectbox' => array(
            'select[name="dbform[is_countable]"]' => 'любое',
            'select[name="dbform[code]"]' => 'АТС',
        ), 
        'input' => array(
            'input[name="dbform[description]"]' => 'test_extra',
            'input[name="dbform[price]"]' => '300',
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'itpark' => array(
        'page' => '?module=tarifs&action=itpark',
        'add_link' => 'Добавить',
        'selectbox' => array(
            'select[name="dbform[okvd_code]"]' => 'мин',
            'select[name="dbform[code]"]' => 'Конференц-зал',
            'select[name="dbform[period]"]' => 'ежемесячно',
        ), 
        'input' => array(
            'input[name="dbform[description]"]' => 'test_itpark',
            'input[name="dbform[price]"]' => '300',
        ),
        'button' => 'Добавить'
    ),
    'welltime' => array(
        'page' => '?module=tarifs&action=welltime',
        'add_link' => 'Добавить',
        'selectbox' => array(
            'select[name="dbform[period]"]' => 'ежемесячно',
        ), 
        'input' => array(
            'input[name="dbform[description]"]' => 'test_welltime',
            'input[name="dbform[price]"]' => '300',
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'vpbx' => array(
        'page' => '?module=tarifs&action=virtpbx',
        'add_link' => 'Добавить',
        'selectbox' => array(
            'select[name="dbform[is_fax]"]' => 'Нет',
        ), 
        'input' => array(
            'input[name="dbform[description]"]' => 'test_vpbx',
            'input[name="dbform[price]"]' => '300',
            'input[name="dbform[overrun_per_port]"]' => '20',
            'input[name="dbform[overrun_per_mb]"]' => '100',
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    '8800' => array(
        'page' => '?module=tarifs&action=8800',
        'add_link' => 'Добавить',
        'input' => array(
            'input[name="dbform[description]"]' => 'test_8800',
            'input[name="dbform[price]"]' => '300',
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'sms' => array(
        'page' => '?module=tarifs&action=sms',
        'add_link' => 'Добавить',
        'input' => array(
            'input[name="dbform[description]"]' => 'test_sms',
            'input[name="dbform[per_month_price]"]' => '100',
            'input[name="dbform[per_sms_price]"]' => '0.5',
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'WellSystem' => array(
        'page' => '?module=tarifs&action=wellsystem',
        'add_link' => 'Добавить',
        'input' => array(
            'input[name="dbform[description]"]' => 'test_wellsystem',
            'input[name="dbform[price]"]' => '300',
        ),
        'button' => 'Добавить'
    ),
);

foreach ($tariffs as $v)
{
    $I->amOnPage($v['page']);

    // ссылка на добавление нового тарифа
    $I->seeLink($v['add_link']);
    $I->click($v['add_link']);

    // заполнение селектбоксов формы
    if (isset($v['selectbox'])) {
        foreach ($v['selectbox'] as $select => $option) {
            $I->selectOption($select, $option);
        }
    }
    
    // заполнение текстовый полей формы
    if (isset($v['input'])) {
        foreach ($v['input'] as $field => $value) {
            $I->fillField($field, $value);
        }
    }
    
    // выбор чекбоксов формы
    if (isset($v['checkbox'])) {
        foreach ($v['checkbox'] as $option) {
            $I->checkOption($option);
        }
    }
    
    // жмем кнопку "Добавить"
    $I->click($v['button']);
    
    //проверка
    if (isset($v['verifications'])) {
        foreach ($v['verifications'] as $type => $verification) {
            // проверка ссылок на тарифы телефонии по введеному имени
            if ($type == 'link') {
                $I->seeLink($verification);
            }
            
            // проверка текста короткого имени тарифа
            if ($type == 'text') {
                $I->see($verification);
            }
            
            //проверка введенных значений селектбоксев
            if ($type == 'selectboxes' && isset($v['selectbox'])) {
                foreach ($v['selectbox'] as $select => $option)
                {
                    $I->seeOptionIsSelected($select, $option);
                }
            }
            
            //проверка введеных значений в текстовые поля
            if ($type == 'inputs' && isset($v['input'])) {
                foreach ($v['input'] as $field => $value)
                {
                    $I->seeInField($field, $value);
                }
            }
        }
    }
    
}


        

