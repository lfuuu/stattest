<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\important_events\ImportantEvents;

/**
 * @property string $name
 */
class BalanceProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_BALANCE = 'balance';

    private $value = 0;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $this->value = $this->setPropertyName('balance')->getValue();
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_BALANCE => 'Баланс на момент события',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_BALANCE => $this->getValue(),
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return number_format($this->value, 2, '.', '');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return Html::tag('b', self::labels()[self::PROPERTY_BALANCE] . ': ') . $this->getValue();
    }

}