<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\ClientSuper;
use app\models\important_events\ImportantEvents;

class SuperClientProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_SUPER_ID = 'super.id';
    const PROPERTY_SUPER_NAME = 'super.name';

    /** @var ClientSuper|null $clientSuper */
    private
        $clientSuper = null;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $clientSuperId = $this->setPropertyName('super_id')->getPropertyValue();
        if (!(int)$clientSuperId) {
            $clientSuperId = $this->setPropertyName('to_super_id')->getPropertyValue();
        }

        $clientSuper = ClientSuper::findOne(['id' => (int)$clientSuperId]);
        if (!is_null($clientSuper)) {
            $this->clientSuper = $clientSuper;
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_SUPER_ID => 'ID супер-клиента',
            self::PROPERTY_SUPER_NAME => 'Супер-клиент',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_SUPER_ID => $this->getValue(),
            self::PROPERTY_SUPER_NAME => $this->getName(),
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return (!is_null($this->clientSuper) ? $this->clientSuper->id : 0);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (!is_null($this->clientSuper) ? $this->clientSuper->name : '');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (!$this->getValue()) {
            return '';
        }

        return
            Html::tag('b', self::labels()[self::PROPERTY_SUPER_NAME] . ': ') .
            $this->getName();
    }

}