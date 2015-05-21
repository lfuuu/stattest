<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\classes\behaviors\HistoryVersion;
use app\classes\behaviors\HistoryChanges;

class ClientContract extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contract';
    }

    public function attributeLabels()
    {
        return [
            'number' => '№ договора',
            'organization' => 'Организация',
            'manager' => 'Менеджер',
            'account_manager' => 'Аккаунт менеджер',
            'business_process_id' => 'Бизнес процесс',
            'business_process_status_id' => 'Статус бизнес процесса',
            'comment' => 'Комментарий',
            'contract_type_id' => 'Тип',
        ];
    }

    public function behaviors()
    {
        return [
            HistoryVersion::className(),
            HistoryChanges::className(),
        ];
    }
}
