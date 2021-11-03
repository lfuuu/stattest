<?php
namespace app\forms\usage;

use Yii;
use yii\db\Expression;
use app\classes\Form;
use app\models\UsageTechCpe;

class UsageTechCpeForm extends Form
{

    protected static $formModel = UsageTechCpe::class;

    public
        $id,
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
        $service,
        $id_service,
        $deposit_sumUSD,
        $deposit_sumRUB,
        $snmp,
        $cacti_monitor_url,
        $ast_autoconf;

    public function rules()
    {
        return [
            [['client', 'id_model'], 'required'],
            [
                [
                    'actual_from',
                    'actual_to',
                    'client',
                    'serial',
                    'mac',
                    'ip',
                    'ip_nat',
                    'ip_cidr',
                    'ip_gw',
                    'admin_login',
                    'admin_pass',
                    'numbers',
                    'logins',
                    'service',
                    'cacti_monitor_url',
                ],
                'string'
            ],
            ['owner', 'in', 'range' => ['', 'mcn', 'client', 'mgts']],
            ['tech_support', 'in', 'range' => ['', 'mcn', 'client', 'mgts']],
            [['deposit_sumUSD', 'deposit_sumRUB',], 'number'],
            [['id', 'id_model', 'id_service', 'snmp', 'ast_autoconf',], 'integer'],
            ['serial', 'validateUsingSerialNumber'],
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
            'admin_login' => 'Логин администратора',
            'admin_pass' => 'Пароль администратора',
            'numbers' => 'Номера',
            'logins' => 'Логины',
            'owner' => 'Владелец',
            'tech_support' => 'Тех. поддержка',
            'ast_autoconf' => 'Режим конфигурирования asteriskа',
            'cacti_monitor_url' => 'Мониторинг Cacti',
        ];
    }

    public function validateUsingSerialNumber()
    {
        if ($this->id || $this->serial == '') {
            return false;
        }

        $query = UsageTechCpe::find();

        $query->where(new Expression('id IS NOT NULL'));

        if ((int)$this->id) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        $result = $query
            ->andWhere(['serial' => $this->serial])
            ->andWhere(['<=', 'actual_from', new Expression('NOW()')])
            ->andWhere(['>=', 'actual_to', new Expression('NOW()')])
            ->count();

        if ($result) {
            $this->addError('serial', 'Такой серийный номер занят');
        }
    }

}