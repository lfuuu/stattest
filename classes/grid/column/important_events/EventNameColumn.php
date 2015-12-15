<?php

namespace app\classes\grid\column\important_events;

use app\models\important_events\ImportantEventsNames;

class EventNameColumn extends \kartik\grid\DataColumn
{

    public $label = 'Событие';
    public $attribute = 'event';
    public $value = 'event';
    public $filterType = '\app\widgets\select_multiply\SelectMultiply';
    public $filterInputOptions = null;

    public function __construct($config = [])
    {
        $eventsList = [];

        foreach (ImportantEventsNames::find()->all() as $event) {
            $eventsList[$event->group->title][$event->code] = $event->value;
        }

        $this->filterWidgetOptions['items'] = $eventsList;
        $this->filterWidgetOptions['clientOptions']['multiple'] = true;
        $this->filterWidgetOptions['clientOptions']['placeholder'] = '- Выберите событие(я) -';
        $this->filterWidgetOptions['clientOptions']['width'] = '100%';

        parent::__construct($config);
    }

}