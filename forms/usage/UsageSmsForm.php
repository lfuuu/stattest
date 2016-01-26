<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\UsageSms;

class UsageSmsForm extends Form
{

    protected static $formModel = UsageSms::class;

    public
        $client,
        $status,
        $comment,
        $actual_from,
        $actual_to,
        $tarif_id;

    public function rules()
    {
        return [
            [['tarif_id',], 'number'],
            [['client', 'status', 'comment', 'actual_from', 'actual_to',], 'string'],
            [['client', 'tarif_id',], 'required', 'skipOnEmpty' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client' => 'Клиент',
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'status' => 'Состояние',
            'comment' => 'Комментарий',
            'tarif_id' => 'Услуга',
        ];
    }

}