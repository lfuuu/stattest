<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\UsageIpRoutes;
use app\models\usages\UsageInterface;
use yii\db\Expression;

class UsageIpRoutesForm extends Form
{

    protected static $formModel = UsageIpRoutes::class;

    public
        $id,
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
            [['actual_from', 'actual_to'], 'date', 'format' => 'Y-m-d'],
            [['id', 'port_id', 'gpon_reserv', ], 'integer'],
            ['type', 'in', 'range' => ['unused','uplink','uplink+pool','client','client-nat','pool','aggregate','reserved','gpon']],
            ['net', 'validateUsingNet'],
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

    public function validateUsingNet()
    {
        $query = UsageIpRoutes::find();

        $query->where(new Expression('id IS NOT NULL'));

        if ((int) $this->id) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        $result = $query
            ->andWhere(['net' => $this->net])
            ->andWhere(['!=', 'actual_from', UsageInterface::MAX_POSSIBLE_DATE])
            ->andWhere([
                'or',
                new Expression(':actual_from BETWEEN actual_from AND actual_to'),
                new Expression(':actual_to BETWEEN actual_from AND actual_to'),
                [
                    'and',
                    ['between', 'actual_from', $this->actual_from, $this->actual_to],
                    ['between', 'actual_to', $this->actual_from, $this->actual_to]
                ]
            ], [
                ':actual_from' => $this->actual_from,
                ':actual_to' => $this->actual_to,
            ])->count();

        if ($result) {
            $this->addError('net', 'Сеть уже занята');
        }
    }

}