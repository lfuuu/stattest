<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\UsageExtra;

class UsageExtraForm extends Form
{

    protected static $formModel = UsageExtra::class;

    public
        $client,
        $actual_from,
        $actual_to,
        $param_value,
        $amount,
        $status,
        $comment,
        $tarif_id,
        $code;

    public function rules()
    {
        return [
            [['actual_from', 'actual_to', 'client', 'param_value', 'comment', 'code'], 'string'],
            [['tarif_id'], 'integer'],
            [['amount'], 'number'],
            ['status', 'in', 'range' => ['connecting', 'working']],
            [['client', 'tarif_id',], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client' => 'Клиент',
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'tarif_id' => 'Услуга',
            'comment' => 'Комментарий',
            'code' => 'Группа услуг',
        ];
    }

}