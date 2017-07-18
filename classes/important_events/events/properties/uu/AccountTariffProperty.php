<?php

namespace app\classes\important_events\events\properties\uu;

use app\classes\important_events\events\properties\PropertyInterface;
use app\classes\important_events\events\properties\UnknownProperty;
use app\modules\uu\models\AccountTariff;
use app\classes\Html;
use app\models\important_events\ImportantEvents;

class AccountTariffProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_SERVICE_ID = 'service.id';
    const PROPERTY_SERVICE_NAME = 'service.name';

    /** @var AccountTariff $accountTariff */
    private $accountTariff;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $accountTariffId = $this->setPropertyName('account_tariff_id')->getPropertyValue();

        if ($accountTariffId) {
            $this->accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_SERVICE_ID => 'ID услуги',
            self::PROPERTY_SERVICE_NAME => 'Услуга',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_SERVICE_ID => $this->getValue(),
            self::PROPERTY_SERVICE_NAME => $this->getName(),
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        if (is_null($this->accountTariff)) {
            return 0;
        }

        return $this->accountTariff->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (is_null($this->accountTariff)) {
            return '';
        }

        return $this->accountTariff->getName();
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
            Html::tag('b', 'Услуга: ') . $this->accountTariff->getAccountTariffLink();
    }

}