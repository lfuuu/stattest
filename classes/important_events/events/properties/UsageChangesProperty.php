<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\models\LogUsageHistory;

class UsageChangesProperty extends UnknownProperty implements PropertyInterface
{

    private $usageId = 0;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $this->usageId = $this->setPropertyName('usage_id')->getPropertyValue();
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $fields = LogUsageHistory::findOne(['service_id' => $this->usageId])->fields;

        $changes = '';
        foreach ($fields as $field) {
            $changes .=
                Html::beginTag('tr') .
                    Html::tag('td', $field->field) .
                    Html::tag('td', $field->value_from) .
                    Html::tag('td', $field->value_to) .
                Html::endTag('tr');
        }

        if (empty($changes)) {
            return '';
        }

        $changes =
            Html::beginTag('div', ['class' => 'important-events table-of-changes']) .
                Html::beginTag('table', ['width' => '100%', 'class' => 'table table-bordered']) .
                    Html::beginTag('tr') .
                        Html::tag('th', 'Поле') .
                        Html::tag('th', 'Значение "До"') .
                        Html::tag('th', 'Значение "После"') .
                    Html::endTag('tr') .
                    $changes .
                Html::endTag('table') .
            Html::endTag('div');

        return $changes;
    }

}