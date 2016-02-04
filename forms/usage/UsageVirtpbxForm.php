<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\UsageVirtpbx;

class UsageVirtpbxForm extends Form
{

    protected static $formModel = UsageVirtpbx::class;

    public
        $client,
        $region,
        $actual_from,
        $actual_to,
        $amount,
        $status,
        $comment,
        $moved_from;

    public function rules()
    {
        return [
            [['client', 'region'], 'required'],
            [['actual_from', 'actual_to', 'client', 'comment'], 'string'],
            [['amount'], 'number'],
            [['region', 'moved_from'], 'integer'],
            ['status', 'in', 'range' => ['connecting', 'working']],
            ['moved_from', 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client' => 'Клиент',
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'amount' => 'Количество',
            'comment' => 'Комментарий',
            'status' => 'Состояние',
            't_id_tarif' => 'Тариф',
        ];
    }

}
