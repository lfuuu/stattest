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
        $tarif_id,
        $is_moved,
        $moved_from;

    public function rules()
    {
        return [
            [['client'], 'required'],
            [['actual_from', 'actual_to', 'client', 'comment'], 'string'],
            [['amount'], 'number'],
            [['tarif_id', 'is_moved', 'moved_from'], 'integer'],
            ['status', 'in', 'range' => ['connecting', 'working']],
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
        ];
    }

}