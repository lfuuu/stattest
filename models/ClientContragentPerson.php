<?php
namespace app\models;

use app\classes\model\HistoryActiveRecord;

class ClientContragentPerson extends HistoryActiveRecord
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
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
        ];
    }

    /**
     * @return $this
     */
    public function loadVersionOnDate($date)
    {
        return HistoryVersion::loadVersionOnDate($this, $date);
    }
}
