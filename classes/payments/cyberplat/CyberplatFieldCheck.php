<?php

namespace  app\classes\payments\cyberplat;

use app\classes\payments\cyberplat\exceptions\AnswerErrorBadAmount;
use app\classes\payments\cyberplat\exceptions\AnswerErrorClientNotFound;
use app\classes\payments\cyberplat\exceptions\AnswerErrorDate;
use app\classes\payments\cyberplat\exceptions\AnswerErrorReceipt;
use app\classes\payments\cyberplat\exceptions\AnswerErrorType;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Payment;

class CyberplatFieldCheck
{
    const PAYMENT_SUM_MIN = 10;
    const PAYMENT_SUM_MAX = 15000;

    private $_organizationIds = null;
    public $organizationId = null;

    /**
     * CyberplatFieldCheck constructor.
     *
     * @param integer[] $organizationIds
     */
    public function __construct($organizationIds)
    {
        $this->_organizationIds = $organizationIds;
    }

    /**
     * Проверка типа
     *
     * @param array $data
     * @throws AnswerErrorType
     */
    public function assertType(&$data)
    {
        if (!$data["type"] || $data["type"] != 1) {
            throw new AnswerErrorType();
        }
    }

    /**
     * Проверка суммы
     *
     * @param array $data
     * @throws AnswerErrorBadAmount
     */
    public function assertAmount(&$data)
    {
        if (!$data["amount"]) {
            throw new AnswerErrorBadAmount();
        }

        $data["amount"] = (float)@floatval($data["amount"]);

        if ($data["amount"] > self::PAYMENT_SUM_MAX || $data["amount"] < self::PAYMENT_SUM_MIN) {
            throw new AnswerErrorBadAmount();
        }
    }

    /**
     * Проверка номера клиента
     *
     * @param array $data
     * @throws AnswerErrorClientNotFound
     * @return ClientAccount
     */
    public function assertNumber(&$data)
    {
        if (!$data["number"] || !preg_match("/^\d{1,6}$/", $data["number"])) {
            throw new AnswerErrorClientNotFound();
        }

        /** @var ClientAccount $account */
        $account = ClientAccount::findOne([(is_numeric($data["number"]) ? 'id' : 'client') => ($data["number"])]);

        // Не найден ЛС или организация не совпадает
        if (!$account || !$account->contract || !in_array($account->contract->organization_id, $this->_organizationIds)) {
            throw new AnswerErrorClientNotFound();
        }

        $this->organizationId = $account->contract->organization_id;

        return $account;
    }

    /**
     * Проверка номера платежа
     *
     * @param array $data
     * @throws AnswerErrorReceipt
     */
    public function assertReceipt(&$data)
    {
        $r = $data["receipt"];

        if (!$r || !preg_match("/^\d{3,15}$/", $r)) {
            throw new AnswerErrorReceipt();
        }
    }

    /**
     * Платеж уже добавлен?
     *
     * @param array $data
     * @return bool
     */
    public function isReceiptAdded(&$data)
    {
        return Payment::find()
            ->where([
                'payment_no' => $data["receipt"]
            ])
            ->exists();
    }

    /**
     * Проверка даты платежа
     *
     * @param array $data
     * @return \ActiveRecord\DateTime
     * @throws AnswerErrorDate
     */
    public function assertDate(&$data)
    {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/", $data["date"])) {
            throw new AnswerErrorDate();
        }

        $date = \DateTime::createFromFormat(DateTimeZoneHelper::ISO8601_WITHOUT_TIMEZONE, $data['date']);

        return $date->format(DateTimeZoneHelper::DATE_FORMAT);
    }
}
