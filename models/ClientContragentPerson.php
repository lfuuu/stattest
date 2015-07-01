<?php
namespace app\models;

use yii\db\ActiveRecord;

class ClientContragentPerson extends ActiveRecord
{
    public $historyVersionDate = null;

    public static function tableName()
    {
        return 'client_contragent_person';
    }

    public function attributeLabels()
    {
        return [
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'passport_date_issued' => 'Дата выдачи паспорта',
            'passport_serial' => 'Серия паспорта',
            'passport_number' => 'Номер паспорта',
            'passport_issued' => 'Кем выдан паспорт',
            'registration_address' => 'Адрес регистрации',
        ];
    }

    public function behaviors()
    {
        return [
            'HistoryVersion' => \app\classes\behaviors\HistoryVersion::className(),
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            return parent::save($runValidation = true, $attributeNames = null);
        }
        else {
            if (substr(php_sapi_name(), 0, 3) == 'cli' || !\Yii::$app->request->post('deferred-date') || \Yii::$app->request->post('deferred-date') === date('Y-m-d')) {
                return parent::save($runValidation = true, $attributeNames = null);
            } else {
                $behaviors = $this->behaviors;
                unset($behaviors['HistoryVersion']);
                $behaviors = array_keys($behaviors);
                foreach ($behaviors as $behavior)
                    $this->detachBehavior($behavior);
                $this->beforeSave(false);
            }
            return true;
        }
    }
}
