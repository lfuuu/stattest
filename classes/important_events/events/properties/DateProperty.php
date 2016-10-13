<?php

namespace app\classes\important_events\events\properties;

use Yii;
use DateTime;
use DateTimeZone;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\important_events\ImportantEvents;

class DateProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_DATE = 'date';
    const PROPERTY_DATE_FORMATTED = 'date.formatted';

    private
        $date,
        $formattedDate;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $this->date = $event->date;
        $this->formattedDate =
            Yii::$app->formatter->asDatetime(
                (new DateTime($this->date))
                    ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
            );
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_DATE => 'Когда произошло',
            self::PROPERTY_DATE_FORMATTED => 'Когда произошло форматированное (например: 19 сент. 2016 г., 2:43:16)',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_DATE => $this->getValue(),
            self::PROPERTY_DATE_FORMATTED => $this->getFormattedValue()
        ];
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getFormattedValue()
    {
        return $this->formattedDate;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return Html::tag('b', self::labels()[self::PROPERTY_DATE] . ': ') . $this->getFormattedValue();
    }

}