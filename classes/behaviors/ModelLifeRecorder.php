<?php

namespace app\classes\behaviors;

use app\models\ModelLifeLog;
use app\modules\uu\models\AccountTariff;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\BaseActiveRecord;

/**
 * Class ModelLifeRecorder
 */
class ModelLifeRecorder extends Behavior
{
    public ?string $modelName = null;

    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => 'doRegister',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'doRegister',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'doRegister',
        ];
    }

    /**
     * Установка метки времени
     */
    public function doRegister(Event $event)
    {
        /** @var AccountTariff $sender */
        $sender = $event->sender;
        if ($this->modelName === null) {
            $this->modelName = $sender::tableName();
        }

        switch ($event->name) {
            case BaseActiveRecord::EVENT_AFTER_UPDATE:
                $action = ModelLifeLog::DO_UPDATE;
                break;
            case BaseActiveRecord::EVENT_BEFORE_DELETE:
                $action = ModelLifeLog::DO_DELETE;
                break;
            default:
                $action = ModelLifeLog::DO_INSERT;
        }

        ModelLifeLog::log($this->modelName, $id = $sender->id, $action);
    }
}