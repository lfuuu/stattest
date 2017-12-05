<?php

namespace app\classes\behaviors;

use app\dao\LogBillDao;
use app\models\Bill;
use app\models\Currency;
use app\models\User;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * Class BillChangeLog
 */
class BillChangeLog extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'billCreate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'billUpdate'
        ];
    }

    /**
     * Обработчик создания счета
     *
     * @param Event $event
     * @return bool
     */
    public function billCreate(Event $event)
    {
        return $this->_billLog($event, $isCreate = true);
    }

    /**
     * Обработчик редактирования счета
     *
     * @param Event $event
     * @return bool
     */
    public function billUpdate(Event $event)
    {
        return $this->_billLog($event, $isCreate = false);
    }

    /**
     * Создает запись в логе
     *
     * @param Event $event
     * @param bool $isCreate
     * @return bool
     */
    private function _billLog(Event $event, $isCreate)
    {
        if (!$isCreate && !$event->changedAttributes) {
            return true;
        }

        /** @var Bill $bill */
        $bill = $event->sender;
        $userUserId = null;
        if ($bill->creatorId) {
            $userUserId = $bill->creatorId;
        } else {
            $userUserId = \Yii::$app->user->identity ? \Yii::$app->user->identity->id : User::SYSTEM_USER_ID;
        }


        if ($bill->logMessage !== null) {
            $message = $bill->logMessage;
        } elseif ($isCreate) {
            $message = "Счет создан.";
        } else {
            $message = "Счет обновлен.";
        }

        $message .= "Сумма: " . Currency::formatCurrency($bill->sum, $bill->currency);

        LogBillDao::me()->log($bill->bill_no, $message, $userUserId);

        return true;
    }
}