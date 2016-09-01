<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int id
 * @property string date timestamp
 * @property string event
 * @property string param
 * @property string status 	enum('plan','ok','error','stop')
 * @property int iteration
 * @property string next_start timestamp
 * @property string log_error
 * @property string code
 */
class EventQueue extends ActiveRecord
{
    public static function tableName()
    {
        return 'event_queue';
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Время запуска',
            'next_start' => 'Время следующего запуска',
            'event' => 'Событие',
            'param' => 'Параметры',
            'status' => 'Статус',
            'iteration' => 'Кол-во попыток',
            'log_error' => 'Лог ошибок',
            'code' => 'Код',
        ];
    }

}