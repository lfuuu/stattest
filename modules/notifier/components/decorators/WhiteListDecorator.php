<?php

namespace app\modules\notifier\components\decorators;

use app\models\important_events\ImportantEventsNames;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * @property string $result
 * @property array $items
 * @property array $log
 *
 * @property-read LogDecorator[] $prettyLog
 * @property-read ArrayDataProvider $dataProvider
 */
class WhiteListDecorator extends Model
{

    /** @var string */
    public $result;

    /** @var array */
    public $items;

    /** @var array */
    public $log;

    /** @var LogDecorator[] */
    public $prettyLog;

    /** @var bool */
    public $isAvailable = false;

    /** @var array */
    private $_data = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        foreach ($this->log as $record) {
            $this->prettyLog[] = new LogDecorator($record);
        }

        if (count($this->items)) {
            $this->items = ArrayHelper::map($this->items, 'event_type_id', 'created_at');
        }

        foreach (ImportantEventsNames::find()->each() as $event) {
            $isAvailableEvent = array_key_exists($event->code, $this->items);

            if ($this->isAvailable && !$isAvailableEvent) {
                continue;
            }

            $this->_data[] = (new WhiteListEventDecorator($event))
                ->setActivity($isAvailableEvent ? $this->items[$event->code] : 0);
        }
    }

    /**
     * @return ArrayDataProvider
     */
    public function getDataProvider()
    {
        return new ArrayDataProvider([
            'allModels' => $this->_data,
            'sort' => false,
            'pagination' => false,
        ]);
    }

}