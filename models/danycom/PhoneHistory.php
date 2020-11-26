<?php

namespace app\models\danycom;

use app\classes\model\ActiveRecord;

/**
 * Class PhoneHistory
 * @property string $process_id
 * @property string $date_request
 * @property string $phone_contact
 * @property int $number
 * @property string $phone_ported
 * @property string $process_type
 * @property string $from
 * @property string $to
 * @property string $state
 * @property string $state_current
 * @property string $region
 * @property string $date_ported
 * @property string $last_message
 * @property string $date_sent
 * @property string $last_sender
 * @property int $code
 * @property string $created_at
 */
class PhoneHistory extends ActiveRecord
{

    public static function tableName()
    {
        return 'dc_phones_history';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'process_id' => 'NP ID',
            'date_request' => 'Дата запроса',
            'phone_contact' => 'Контактный номер',
            'number' => 'Вычисление номера',
            'phone_ported' => 'Номер',
            'process_type' => 'Тип процесса',
            'from' => 'Запрашивающий',
            'to' => 'Ответчик',
            'state' => 'Статус',
            'region' => 'Регион',
            'date_ported' => 'Дата портирования',
            'last_message' => 'Последнее сообщение',
            'date_sent' => 'Дата последнего сообщения',
            'last_sender' => 'Последний oтправитель',
            'code' => 'Код состояния',
            'created_at' => 'Дата заливки',
        ];
    }
}
