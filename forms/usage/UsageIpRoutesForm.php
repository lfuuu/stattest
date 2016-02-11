<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\UsageIpRoutes;

class UsageIpRoutesForm extends Form
{

    protected static $formModel = UsageIpRoutes::class;

    public
        $actual_from,
        $actual_to,
        $type,
        $port_id,
        $net,
        $nat_net,
        $dnat,
        $up_node,
        $flows_node,
        $comment,
        $gpon_reserv;

    public function rules()
    {
        return [
            [['port_id', 'net'], 'required'],
            [
                [
                    'actual_from', 'actual_to', 'net', 'nat_net',
                    'dnat', 'up_node', 'flows_node', 'comment'
                ], 'string'
            ],
            [['port_id', 'gpon_reserv', ], 'integer'],
            ['type', 'in', 'range' => ['unused','uplink','uplink+pool','client','client-nat','pool','aggregate','reserved','gpon']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'net' => 'IP-адрес сети',
            'nat_net' => 'IP-адрес внутренней сети (via NAT)',
            'dnat' => 'dnat',
            'flows_node' => 'dnat',
            'up_node' => 'up_node',
            'gpon_reserv' => 'Сеть под GPON',
            'comment' => 'Комментарий',
        ];
    }

}