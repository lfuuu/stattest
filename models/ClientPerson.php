<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class ClientPerson extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_person';
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

    public function rules()
    {
        return [
            [
                [
                    'first_name',
                    'last_name',
                    'middle_name',
                    'passport_date_issued',
                    'passport_serial',
                    'passport_number',
                    'passport_issued',
                    'registration_address'
                ], 'string'
            ],
        ];
    }
}
