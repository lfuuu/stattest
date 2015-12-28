<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\UsageWelltime;

class UsageWelltimeForm extends Form
{

    protected static $formModel = UsageWelltime::class;

    public
        $client,
        $ip,
        $status,
        $comment,
        $actual_from,
        $actual_to,
        $amount,
        $router,
        $tarif_id;

    public function rules()
    {
        return [
            [['client'], 'required'],
            [
                [
                    'client', 'ip', 'status', 'comment',
                    'actual_from', 'actual_to', 'amount', 'router'
                ], 'string'
            ],
            [['tarif_id',], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client' => 'Клиент',
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'ip' => 'IP',
            'amount' => 'Количество',
            'status' => 'Состояние',
            'comment' => 'Комментарий',
            'tarif_id' => 'Тариф',
            'router' => 'Роутер',
        ];
    }

}