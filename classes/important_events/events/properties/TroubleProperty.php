<?php

namespace app\classes\important_events\events\properties;

use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\models\Trouble;

class TroubleProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_TROUBLE_ID = 'trouble.id';
    const PROPERTY_TROUBLE_BILL_NO = 'trouble.bill_no';
    const PROPERTY_TROUBLE_PROBLEM = 'trouble.problem';

    /** @var Trouble|null $trouble */
    private
        $trouble = null;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $troubleId = $this->setPropertyName('trouble_id')->getPropertyValue();

        $trouble = Trouble::findOne(['id' => $troubleId]);
        if (!is_null($trouble)) {
            $this->trouble = $trouble;
        }
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return [
            self::PROPERTY_TROUBLE_ID => 'ID заявки',
            self::PROPERTY_TROUBLE_BILL_NO => '№ счета',
            self::PROPERTY_TROUBLE_PROBLEM => 'Содержание',
        ];
    }

    /**
     * @return array
     */
    public function methods()
    {
        return [
            self::PROPERTY_TROUBLE_ID => $this->getValue(),
            self::PROPERTY_TROUBLE_BILL_NO => $this->getBillNo(),
            self::PROPERTY_TROUBLE_PROBLEM => $this->getProblem(),
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return (!is_null($this->trouble) ? $this->trouble->id : 0);
    }

    /**
     * @return string
     */
    public function getBillNo()
    {
        return (!is_null($this->trouble) ? $this->trouble->bill_no : '');
    }

    /**
     * @return string
     */
    public function getProblem()
    {
        return (!is_null($this->trouble) ? $this->trouble->problem : '');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if (!$this->getValue()) {
            return '';
        }

        if ($this->getBillNo()) {
            return
                Html::tag('b', 'Счет №' . $this->getBillNo() . ': ') .
                Html::a(
                    $this->getBillNo(),
                    ['/', 'module' => 'newaccounts', 'action' => 'bill_view', 'bill' => $this->getBillNo()],
                    ['target' => '_blank']
                );
        } else {
            return
                Html::tag('b', 'Заявка №' . $this->getValue() . ': ') .
                Html::a(
                    $this->getProblem(),
                    ['/', 'module' => 'tt', 'action' => 'view', 'id' => $this->getValue()],
                    ['target' => '_blank']
                );
        }
    }

}