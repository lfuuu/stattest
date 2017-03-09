<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class EventQueue
 *
 * @property int $id
 * @property string $timestamp
 * @property string $event
 * @property string $param
 * @property string $status
 * @property int $iteration
 * @property string $next_start
 * @property string $log_error
 * @property string $code
 * @property string $insert_time
 */
class EventQueue extends ActiveRecord
{
    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';
    const STATUS_PLAN = 'plan';
    const STATUS_STOP = 'stop';

    public static $statuses = [
        self::STATUS_PLAN => 'Запланировано',
        self::STATUS_OK => 'Выполнено',
        self::STATUS_ERROR => 'Временная ошибка',
        self::STATUS_STOP => 'Постоянная ошибка',
    ];

    const ITERATION_MAX_VALUE = 20;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'event_queue';
    }

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице = Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'insert_time' => 'Время создания',
            'date' => 'Запуск',
            'next_start' => 'Следующий запуск',
            'event' => 'Событие',
            'param' => 'Параметры',
            'status' => 'Статус',
            'iteration' => 'Кол-во попыток',
            'log_error' => 'Лог ошибок',
            'code' => 'Код',
        ];
    }
}