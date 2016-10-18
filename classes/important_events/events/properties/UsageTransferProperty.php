<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\usages\UsageFactory;

class UsageTransferProperty extends UnknownProperty implements PropertyInterface
{

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
        $usageCode = $this->setPropertyName('usage')->getPropertyValue();
        $usageId = $this->setPropertyName('usage_id')->getPropertyValue();

        if (!$usageCode || !$usageId) {
            return '';
        }

        $fromUsage = UsageFactory::getUsage($usageCode)->findOne(['id' => $usageId]);
        $toUsage = UsageFactory::getUsage($usageCode)->findOne(['id' => (string)$fromUsage['next_usage_id']]);

        list($value) = $fromUsage->helper->description;

        return
            Html::tag('b', 'Услуга: ') .
            Html::a($value, $toUsage->helper->editLink, ['target' => '_blank']) .
            ' перемещана от ' .
            Html::a($fromUsage->clientAccount->contragent->name, ['/client/view', 'id' => $fromUsage->clientAccount->id], ['target' => '_blank']) .
            ' к ' .
            Html::a($toUsage->clientAccount->contragent->name, ['/client/view', 'id' => $toUsage->clientAccount->id], ['target' => '_blank']);
    }

}