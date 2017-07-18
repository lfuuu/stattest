<?php

namespace app\classes\important_events\events\properties\uu;

use app\classes\important_events\events\properties\PropertyInterface;
use app\classes\important_events\events\properties\UnknownProperty;
use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\modules\uu\models\ServiceType;
use yii\base\InvalidParamException;

class ServiceTypeProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_SERVICE_TYPE_NAME = 'service.type';

    /** @var ServiceType $serviceType */
    private $serviceType;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $serviceTypeId = $this->setPropertyName('service_type_id')->getPropertyValue();

        if ($serviceTypeId) {
            $this->serviceType = ServiceType::findOne(['id' => $serviceTypeId]);
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_SERVICE_TYPE_NAME => 'Тип услуги',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_SERVICE_TYPE_NAME => $this->getName(),
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (is_null($this->serviceType)) {
            return '';
        }

        return $this->serviceType->name;
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function getDescription()
    {
        if (!$this->getValue()) {
            return '';
        }

        return
            Html::tag('b', 'Тип услуги: ') .
            Html::a($this->getName(), $this->serviceType->getUrl(), ['target' => '_blank']);
    }

}