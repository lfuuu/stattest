<?php

namespace app\classes\behaviors;

use app\models\EventQueue;
use yii\base\Behavior;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Универсальное поведение для добавления задачи в очередь по базовым событиям модели
 * Class EventQueueAddEvent
 *
 * @property string $insertEvent
 * @property string $updateEvent
 * @property string $deleteEvent
 * @property string $idField
 */
class EventQueueAddEvent extends Behavior
{
    /**
     * Событие, генерируемое при вставке
     */
    public $insertEvent = null;

    /**
     * Событие, генерируемое при обновлении
     */
    public $updateEvent = null;

    /**
     * Событие, генерируемое при удалении
     */
    public $deleteEvent = null;

    /**
     * Primary key field
     */
    public $idField = 'id';

    /**
     * Привязка события к индикатору
     */
    public $isWithIndicator = false;


    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * After insert
     */
    public function afterInsert()
    {
        if (!$this->insertEvent) {
            return;
        }

        if ($this->isWithIndicator) {
            EventQueue::goWithIndicator($this->insertEvent, $this->owner->{$this->idField}, $this->_getTableName(), $this->owner->{$this->idField});
        } else {
            EventQueue::go($this->insertEvent, $this->owner->{$this->idField});
        }
    }

    /**
     * After update
     */
    public function afterUpdate()
    {
        if (!$this->updateEvent) {
            return;
        }

        if ($this->isWithIndicator) {
            EventQueue::goWithIndicator($this->updateEvent, $this->owner->{$this->idField}, $this->_getTableName(), $this->owner->{$this->idField});
        } else {
            EventQueue::go($this->updateEvent, $this->owner->{$this->idField});
        }
    }

    /**
     * After delete
     */
    public function afterDelete()
    {
        if (!$this->deleteEvent) {
            return;
        }

        if ($this->isWithIndicator) {
            EventQueue::goWithIndicator($this->deleteEvent, $this->owner->{$this->idField}, $this->_getTableName(), $this->owner->{$this->idField});
        } else {
            EventQueue::go($this->deleteEvent, $this->owner->{$this->idField});
        }
    }

    /**
     * Получение название таблицы для связи с индикатором
     *
     * @return string
     */
    private function _getTableName()
    {
        /** @var Model $owner */
        $owner = $this->owner;
        $model = get_class($owner);
        return $model::tableName();
    }
}
