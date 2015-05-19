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
            'signer_name' => 'ФИО лица, подписывающего договор',
            'signer_position' => 'Должность лица, подписывающего договор',
            'signer_nameV' => 'Должность лица, подписывающего договор, в вин. падеже',
            'signer_positionV' => 'ФИО лица, подписывающего договор, в вин. падеже',
            'contract_type_id' => 'Тип',
            'status' => 'Status',////??
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
