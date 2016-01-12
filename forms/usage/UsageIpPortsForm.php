<?php
namespace app\forms\usage;

use Yii;
use DateTime;
use app\classes\Form;
use app\models\UsageIpPorts;

class UsageIpPortsForm extends Form
{

    protected static $formModel = UsageIpPorts::class;

    public
        $client,
        $actual_from,
        $actual_to,
        $address,
        $port_id,
        $date_last_writeoff,
        $status,
        $speed_mgts,
        $speed_update,
        $amount;

    public function rules()
    {
        return [
            [['client'], 'required'],
            [
                [
                    'actual_from', 'actual_to', 'client', 'address', 'date_last_writeoff',
                    'speed_mgts', 'speed_update'
                ], 'string'
            ],
            [['port_id', 'amount'], 'integer'],
            ['status', 'in', 'range' => ['connecting', 'working']],
            [['date_last_writeoff', 'speed_update'], 'default', 'value' => (new DateTime())->format('Y-m-d H:i:s')],
            ['speed_mgts', 'default', 'value' => ''],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client' => 'Клиент',
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'address' => 'Адрес',
            'amount' => 'Количество',
        ];
    }

}