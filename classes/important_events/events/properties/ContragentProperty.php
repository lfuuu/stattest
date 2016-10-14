<?php

namespace app\classes\important_events\events\properties;

use yii\helpers\Url;
use app\classes\Html;
use app\models\ClientContragent;
use app\models\important_events\ImportantEvents;

class ContragentProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_CONTRAGENT_ID = 'contragent.id';
    const PROPERTY_CONTRAGENT_NAME = 'contragent.name';

    /** @var ClientContragent|null $accountContraget */
    private
        $accountContragent = null;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $contragentId = $this->setPropertyName('contragent_id')->getPropertyValue();
        if (!(int)$contragentId) {
            $contragentId = $this->setPropertyName('to_contragent_id')->getPropertyValue();
        }

        $accountContragent = ClientContragent::findOne(['id' => (int)$contragentId]);
        if (!is_null($accountContragent)) {
            $this->accountContragent = $accountContragent;
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CONTRAGENT_ID => 'ID контрагента',
            self::PROPERTY_CONTRAGENT_NAME => 'Контрагент',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_CONTRAGENT_ID => $this->getValue(),
            self::PROPERTY_CONTRAGENT_NAME => $this->getName(),
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return (!is_null($this->accountContragent) ? $this->accountContragent->id : 0);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (!is_null($this->accountContragent) ? $this->accountContragent->name : '');
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
            Html::tag('b', self::labels()[self::PROPERTY_CONTRAGENT_NAME]. ': ') .
            Html::a(
                $this->getName(),
                Url::toRoute(['/contragent/edit', 'id' => $this->getValue()]),
                ['target' => '_blank']
            );
    }

}