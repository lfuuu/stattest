<?php
namespace app\models;

use app\classes\behaviors\uu\SyncAccountTariffLight;
use yii\db\ActiveRecord;

/**
 * @property int id
 * @property string date timestamp
 * @property string event
 * @property string param
 * @property string status    enum('plan','ok','error','stop')
 * @property int iteration
 * @property string next_start timestamp
 * @property string log_error
 * @property string code
 * @property string insert_time
 */
class EventQueue extends ActiveRecord
{
    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';
    const STATUS_PLAN = 'plan';
    const STATUS_STOP = 'stop';

    public static $statuses = [
        self::STATUS_PLAN => 'Запланирована',
        self::STATUS_OK => 'Задача выполнена',
        self::STATUS_ERROR => 'Временная ошибка',
        self::STATUS_STOP => 'Постоянная ошибка',
    ];

    const ITERATION_MAX_VALUE = 20;

    public static function tableName()
    {
        return 'event_queue';
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице = Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'insert_time' => 'Время создания',
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