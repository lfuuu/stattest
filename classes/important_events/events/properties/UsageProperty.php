<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\models\usages\UsageFactory;
use app\models\usages\UsageInterface;

class UsageProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_USAGE_ID = 'usage.id';
    const PROPERTY_USAGE_NAME = 'usage.name';

    /** @var UsageInterface|null $usage */
    private $usage = null;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $usageCode = $this->setPropertyName('usage')->getPropertyValue();
        $usageId = $this->setPropertyName('usage_id')->getPropertyValue();
        $usageNumber = $this->setPropertyName('number')->getPropertyValue();

        $usage = null;

        if ($usageId) {
            $usage = UsageFactory::getUsage($usageCode)->findOne(['id' => $usageId]);
        } else if($usageCode === 'usage_voip' && $usageNumber) {
            $usage = UsageFactory::getUsage($usageCode)->findOne(['E164' => $usageNumber]);
        }

        if (!is_null($usage)) {
            $this->usage = $usage;
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_USAGE_ID => 'ID услуги',
            self::PROPERTY_USAGE_NAME => 'Наименование услуги',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_USAGE_ID => $this->getValue(),
            self::PROPERTY_USAGE_NAME => $this->getName(),
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return (!is_null($this->usage) ? $this->usage->id : 0);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (!is_null($this->usage) ? $this->usage->helper->title : 0);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (!$this->getValue()) {
            return '';
        }

        list($value) = $this->usage->helper->description;

        return
            Html::tag('b', 'Услуга: ') . Html::tag('u', $this->getName()) . ' ' .
            Html::a($value, $this->usage->helper->editLink, ['target' => '_blank']);
    }

}