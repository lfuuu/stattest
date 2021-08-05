<?php

namespace app\models;

use app\classes\model\HistoryActiveRecord;

/**
 * @property int $contragent_id
 * @property string $last_name
 * @property string $first_name
 * @property string $middle_name
 * @property string $passport_date_issued
 * @property string $passport_serial
 * @property string $passport_number
 * @property string $passport_issued
 * @property string $registration_address
 * @property string $mother_maiden_name
 * @property string $birthplace
 * @property string $birthday
 * @property string $other_document
 */
class ClientContragentPerson extends HistoryActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_contragent_person';
    }

    public function attributeLabels()
    {
        return [
            'contragent_id' => 'Конатргент',
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'passport_date_issued' => 'Паспорт выдан',
            'passport_serial' => 'Паспорт серия',
            'passport_number' => 'Паспорт номер',
            'passport_issued' => 'Паспорт выдан',
            'registration_address' => 'Адрес прописки',
            'mother_maiden_name' => 'Имя матери',
            'birthplace' => 'Место рождения',
            'birthday' => 'День рождения',
            'other_document' => 'Другой документ',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }
}
