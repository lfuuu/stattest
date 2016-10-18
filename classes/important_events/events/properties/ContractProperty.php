<?php

namespace app\classes\important_events\events\properties;

use yii\helpers\Url;
use app\classes\Html;
use app\models\ClientContract;
use app\models\important_events\ImportantEvents;

class ContractProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_CONTRACT_ID = 'contract.id';
    const PROPERTY_CONTRACT_NUMBER = 'contract.number';

    /** @var ClientContract|null $accountContract */
    private
        $accountContract = null;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $contractId = $this->setPropertyName('contract_id')->getPropertyValue();

        $accountContract = ClientContract::findOne(['id' => (int)$contractId]);
        if (!is_null($accountContract)) {
            $this->accountContract = $accountContract;
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CONTRACT_ID => 'ID договора',
            self::PROPERTY_CONTRACT_NUMBER => 'Номер договора',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_CONTRACT_ID => $this->getValue(),
            self::PROPERTY_CONTRACT_NUMBER => $this->getNumber(),
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return (!is_null($this->accountContract) ? $this->accountContract->id : 0);
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return (!is_null($this->accountContract) ? $this->accountContract->number : '');
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
            Html::tag('b', 'Договор: ') .
            Html::a(
                'Договор №' . ($this->getNumber() ? : 'Без номера') .
                ' (' . $this->accountContract->organization->name . ')',
                Url::toRoute(['/contract/edit', 'id' => $this->getValue()]),
                ['target' => '_blank']
            );
    }

}