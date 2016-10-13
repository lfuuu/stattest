<?php

namespace app\classes\important_events\events\properties;

use Yii;
use app\classes\Html;
use app\classes\IpUtils;
use app\models\important_events\ImportantEvents;

/**
 * @property string $date
 */
class IpProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_IP = 'ip';

    private $ip;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $this->ip = IpUtils::dtr_ntop($event->from_ip);
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_IP => 'IP адрес',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_IP => $this->getValue(),
        ];
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->ip ?: 'не задано';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return Html::tag('b', self::labels()[self::PROPERTY_IP] . ': ') . $this->getValue();
    }

}