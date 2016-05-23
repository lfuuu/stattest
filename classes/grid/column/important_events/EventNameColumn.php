<?php

namespace app\classes\grid\column\important_events;

use app\models\important_events\ImportantEventsNames;

class EventNameColumn extends \kartik\grid\DataColumn
{

    public
        $label = 'Событие',
        $attribute = 'event',
        $value = 'event',
        $filterType = '\app\widgets\multiselect\MultiSelect',
        $filterInputOptions = [];

    private $eventsList = [];

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $eventsList = [];

        foreach (ImportantEventsNames::find()->each() as $event) {
            $eventsList[$event->group->title][$event->code] = $event->value;
            $this->eventsList[$event->code] = $event->value;
        }

        $this->filterWidgetOptions['data'] = $eventsList;
        $this->filterWidgetOptions['nonSelectedText'] = '-- Событие --';
        $this->filterWidgetOptions['clientOptions']['buttonWidth'] = '100%';
        $this->filterWidgetOptions['clientOptions']['enableCollapsibleOptGroups'] = true;
        $this->filterWidgetOptions['clientOptions']['enableClickableOptGroups'] = true;

        $this->filterInputOptions['multiple'] = 'multiple';

        parent::__construct($config);
    }

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        return isset($this->eventsList[$value]) ? $this->eventsList[$value] : $value;
    }

}