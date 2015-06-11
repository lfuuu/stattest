<?php

use tests\codeception\_pages\LoginPage;
use tests\codeception\_pages\NewClient;
use app\tests\codeception\fixtures\TariffVoipFixture;
use app\tests\codeception\fixtures\TariffExtraFixture;
use app\tests\codeception\fixtures\TariffInternetFixture;
use app\tests\codeception\fixtures\TariffSmsFixture;
use app\tests\codeception\fixtures\TariffVirtpbxFixture;

$I = new _WebTester($scenario);
$I->wantTo('Создать услуги');

$loginPage = LoginPage::loginAsAdmin($I);

$from_ts = strtotime('first day of previous month');
$to_ts = strtotime('first day of next month');

$date_activation = date('Y-m-10', $from_ts);
$date_from = date('10-m-Y', $from_ts);
$date_to = date('20-m-Y', $to_ts);

$tariffVoip = new TariffVoipFixture();
$tariffVoip->load();

$tariffExtra = new TariffExtraFixture();
$tariffExtra->load();

$tariffInternet = new TariffInternetFixture();
$tariffInternet->load();

$tariffSms = new TariffSmsFixture();
$tariffSms->load();

$tariffVirtpbx = new TariffVirtpbxFixture();
$tariffVirtpbx->load();

$services = array(
    'virtpbx' => array(
        'page' => '?module=services&action=vo_view',
        'add_link' => 'Добавить телефонный номер',
        'selectbox' => array(
            'select[name="dbform[t_id_tarif]"]' => $tariffVoip->getModel('local')->id,
            'select[name="dbform[status]"]' => 'working',
        ),
        'input' => array(
            'input[name="dbform[actual_from]"]' => $date_from,
            'input[name="dbform[actual_to]"]' => $date_to,
            'input[name="dbform[E164]"]' => '74951059468',
            'input[name="dbform[t_minpayment_group]"]' => 0,
            'input[name="dbform[t_minpayment_local_mob]"]' => 0,
            'input[name="dbform[t_minpayment_russia]"]' => 0,
            'input[name="dbform[t_minpayment_intern]"]' => 0,
            'input[name="dbform[is_moved]"]' => 1,
            'input[name="dbform[is_moved_with_pbx]"]' => 1,
            'input[name="dbform[t_date_activation]"]' => $date_activation,
        ),
        'button' => 'Добавить'
    ),
    'welltime2' => array(
        'page' => '?module=services&action=welltime_view',
        'add_link' => 'Добавить услугу',
        'selectbox' => array(
            'select[name="dbform[tarif_id]"]' => $tariffExtra->getModel('welltime')->id,
            'select[name="dbform[status]"]' => 'working',
            'select[name="dbform[router]"]' => 'arbat4',
        ),
        'input' => array(
            'input[name="dbform[actual_from]"]' => $date_from,
            'input[name="dbform[actual_to]"]' => $date_to,
            'input[name="dbform[ip]"]' => '192.168.100.100',
            'input[name="dbform[amount]"]' => '2',
            'input[name="dbform[comment]"]' => 'Test WellTime',
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'vpbx' => array(
        'page' => '?module=services&action=virtpbx_view',
        'add_link' => 'Добавить услугу',
        'client' => 'virtpbx',
        'selectbox' => array(
            'select[name="dbform[t_id_tarif]"]' => $tariffVirtpbx->getModel('vpbx')->id,
            'select[name="dbform[status]"]' => 'working',
        ),
        'input' => array(
            'input[name="dbform[actual_from]"]' => $date_from,
            'input[name="dbform[actual_to]"]' => $date_to,
            'input[name="dbform[is_moved]"]' => 1,
            'input[name="dbform[comment]"]' => 'Тестовая АТС',
            'input[name="dbform[t_date_activation]"]' => $date_activation,
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'sms2' => array(
        'page' => '?module=services&action=sms_view',
        'add_link' => 'Добавить услугу',
        'selectbox' => array(
            'select[name="dbform[tarif_id]"]' => $tariffSms->getModel('sms')->id,
            'select[name="dbform[status]"]' => 'working',
        ),
        'input' => array(
            'input[name="dbform[actual_from]"]' => $date_from,
            'input[name="dbform[actual_to]"]' => $date_to,
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'internet2' => array(
        'page' => '?module=services&action=in_view',
        'add_link' => 'Добавить подключение',
        'selectbox' => array(
            'select[name="dbform[t_id_tarifIP]"]' => $tariffInternet->getModel('internet')->id,
            'select[name="dbform[status]"]' => 'working',
        ),
        'input' => array(
            'input[name="dbform[actual_from]"]' => $date_from,
            'input[name="dbform[actual_to]"]' => $date_to,
            'input[name="dbform[address]"]' => 'какой-то адрес',
            'input[name="dbform[phone]"]' => '55555555',
            'input[name="dbform[t_date_activation]"]' => $date_activation,
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'collocation2' => array(
        'page' => '?module=services&action=co_view',
        'add_link' => 'Добавить подключение',
        'selectbox' => array(
            'select[name="dbform[t_tarif_type]"]' => 'C',
            'select[name="dbform[t_id_tarifCP]"]' => $tariffInternet->getModel('collocation')->id,
            'select[name="dbform[status]"]' => 'working',
        ),
        'input' => array(
            'input[name="dbform[actual_from]"]' => $date_from,
            'input[name="dbform[actual_to]"]' => $date_to,
            'input[name="dbform[address]"]' => 'какой-то адрес',
            'input[name="dbform[phone]"]' => '6666666666',
            'input[name="dbform[t_date_activation]"]' => $date_activation,
        ),
        'verifications' => array(
            'selectboxes' => true,
            'inputs' => true,
        ),
        'button' => 'Добавить'
    ),
    'email2' => array(
        'page' => '?module=services&action=em_view',
        'add_link' => 'Добавить ящик',
        'selectbox' => array(
            'select[name="dbform[status]"]' => 'working',
            'select[name="dbform[box_quota]"]' => '100000',
        ),
        'input' => array(
            'input[name="dbform[actual_from]"]' => $date_from,
            'input[name="dbform[actual_to]"]' => $date_to,
            'input[name="dbform[local_part]"]' => 'yawic',
            'input[name="dbform[password]"]' => 'yawic',
        ),
        'button' => 'Добавить'
    ),
    'extra2' => array(
        'page' => '?module=services&action=ex_view',
        'add_link' => 'Добавить услугу',
        'form' => '#dbform',
        'params' => array(
            'dbform[actual_from]' => $date_from,
            'dbform[actual_to]' => $date_to,
            'dbform[code]' => 'phone_ats',
            'dbform[tarif_id]' => $tariffExtra->getModel('extra')->id,
            'dbform[status]' => 'working',
            'dbform[client]' => 'extra',
            'dbform[id]' => '',
            'dbform[param_value]' => '',
            'dbform[amount]' => 1,
            'dbform[async_price]' => '',
            'dbform[async_period]' => '',
            'dbform_action' => 'save',
            'module' => 'services',
            'action' => 'ex_apply',
        ),

    ),
);

foreach ($services as $k=>$v)
{
    // создаем клиентов для каждого вида услуг кроме ВАТС(используем клиента с телефонией)
    if (!isset($v['client']) || !$v['client'])
    {
        $newClientPage = NewClient::openBy($I);
        $I->seeCurrentUrlEquals('/index.php?module=clients&action=new');
        $I->see('Регион');

        $client_data = array(
            'client' => $k,
            'company' => $k . ' company by tests',
            'inn' => rand(1000000, 2000000),
            'kpp' => rand(1000000, 2000000),
        );
        $newClientPage->createClient($client_data);
        $client = $k;
    } else {
        $client = $v['client'];
    }
    //переходим на страницу клиента, дабы закрепить fixclient
    $I->amOnPage('/?module=clients&id='.$client);

    //переходим на страницу просмотра существющих услуг "данного типа"
    $I->amOnPage($v['page']);

    //переходим по ссылке для добавления новой услуги
    $I->seeLink($v['add_link']);
    $I->click($v['add_link']);

    // для всех услуг кроме "Доп услуг" мы можем заполнить форму "вручную"
    // для доп услуг не можем из js
    if (!isset($v['form']))
    {
        //заполняем селектбоксы
        if (isset($v['selectbox']) && !empty($v['selectbox']))
        {
            foreach ($v['selectbox'] as $select => $option)
            {
                $I->selectOption($select, $option);
            }
        }
        //заполняем текстовые поля
        if (isset($v['input']) && !empty($v['input']))
        {
            foreach ($v['input'] as $field => $value)
            {
                $I->fillField($field, $value);
            }
        }
        //выбираем чекбоксы
        if (isset($v['checkbox']) && !empty($v['checkbox']))
        {
            foreach ($v['checkbox'] as $option)
            {
                $I->checkOption($option);
            }
        }
        //Жмем кнопку "добавить"
        $I->click($v['button']);
    } else {
        //для "Доп услуг" отправляем целиком всю форму
        $I->submitForm($v['form'], $v['params']);
    }

    // проверки
    switch ($k)
    {
        case 'email2':
            // при создании email'a редиректит на страницу просмотра всех email'ов
            //проверяем дату начала работы ящика и ссылку на ящик
            $I->see(date('Y-m-10', $from_ts));
            $I->seeLink('yawic@mcn.ru');
            break;
        case 'virtpbx':
            // у меня отсутствует связь с доп базами , поэтому переходим на страницу
            // просмотра "телефонических" услуг и проверяем даты и номер
            $I->amOnPage($v['page']);

            $I->see(date('Y-m-10', $from_ts));
            $I->see(date('Y-m-20', $to_ts));
            $I->see('74951059468');
            break;
        case 'extra2':
            // т.к для доп услуг мы отправляли форму, собираем массивы
            // с селектбоксами и текстовыми полями
            $v['selectbox'] = array(
                'select[name="dbform[status]"]' => 'working',
            );
            $v['input'] = array(
                'input[name="dbform[actual_from]"]' => $date_from,
                'input[name="dbform[actual_to]"]' => $date_to,
            );
        default:
            //проверка введенных значений селектбоксев
            if (isset($v['selectbox']) && !empty($v['selectbox']))
            {
                foreach ($v['selectbox'] as $select => $option)
                {
                    if (strpos($select, 'tarif') === false)
                    {
                        $I->seeOptionIsSelected($select, $option);
                    }
                }
            }

            //проверка введеных значений в текстовые поля
            if (isset($v['input']) && !empty($v['input']))
            {
                foreach ($v['input'] as $field => $value)
                {
                    if (strpos($field, 'moved') === false)
                    {
                        $I->seeInField($field, $value);
                    }
                }
            }
    }
}

