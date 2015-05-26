<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\classes\behaviors\HistoryVersion;
use app\classes\behaviors\HistoryChanges;

class ClientContragentPerson extends ActiveRecord
{
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
            HistoryVersion::className(),
            HistoryChanges::className(),
        ];
    }
}
