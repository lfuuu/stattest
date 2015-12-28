<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\UsageTechCpe;

class UsageTechCpeForm extends Form
{

    protected static $formModel = UsageTechCpe::class;

    public
        $actual_from,
        $actual_to,
        $id_model,
        $client,
        $serial,
        $mac,
        $ip,
        $ip_nat,
        $ip_cidr,
        $ip_gw,
        $admin_login,
        $admin_pass,
        $numbers,
        $logins,
        $owner,
        $tech_support,
        $node,
        $service,
        $id_service,
        $deposit_sumUSD,
        $deposit_sumRUB,
        $snmp,
        $ast_autoconf;

    public function rules()
    {
        return [
            [['client'], 'required'],
            [
                [
                    'actual_from', 'actual_to', 'client', 'serial', 'mac',
                    'ip', 'ip_nat', 'ip_cidr', 'ip_gw', 'admin_login', 'admin_pass',
                    'numbers', 'logins', 'node', 'service', ''
                ], 'string'
            ],
            ['owner', 'in', 'range' => ['', 'mcn', 'client', 'mgts']],
            ['tech_support', 'in', 'range' => ['', 'mcn', 'client', 'mgts']],
            [['deposit_sumUSD', 'deposit_sumRUB',], 'number'],
            [['id_model', 'id_service', 'snmp', 'ast_autoconf',], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client' => 'Клиент',
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'id_model' => 'Модель устройства',
            'id_service' => 'Cвязанное подключение',
            'deposit_sumUSD' => 'Сумма залога в USD',
            'deposit_sumRUB' => 'Сумма залога в RUB',
            'serial' => 'Серийный номер',
            'mac' => 'MAC-адрес',
            'ip' => 'IP-адрес',
            'ip_nat' => 'IP-адрес NAT',
            'ip_cidr' => 'IP-адрес CIDR',
            'ip_gw' => 'IP-адрес GW',
            'snmp' => 'SNMP',
            'admin_login' => 'Адмниский логин',
            'admin_pass' => 'Адмниский пароль',
            'numbers' => 'Номера',
            'logins' => 'Логины',
            'owner' => 'Владелец',
            'tech_support' => 'Тех. поддержка',
            'ast_autoconf' => 'Режим конфигурирования asteriskа',
        ];
    }

}