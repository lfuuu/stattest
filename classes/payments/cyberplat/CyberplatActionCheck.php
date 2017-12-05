<?php

namespace app\classes\payments\cyberplat;


use app\classes\payments\cyberplat\exceptions\AnswerErrorSign;
use app\classes\payments\cyberplat\exceptions\AnswerErrorStatus;
use app\classes\payments\cyberplat\exceptions\AnswerOk;
use app\classes\payments\cyberplat\exceptions\AnswerOkPayment;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\Currency;
use app\models\EventQueue;
use app\models\Payment;

class CyberplatActionCheck
{
    private $_organizationIds = null;
    private $_organizationToAuthCode = null;

    /**
     * CyberplatActionCheck constructor.
     *
     * @param integer[] $organizationIds
     * @param integer[] $organizationToAuthCode
     */
    public function __construct($organizationIds, $organizationToAuthCode)
    {
        $this->_organizationIds = $organizationIds;
        $this->_organizationToAuthCode = $organizationToAuthCode;

        $this->fieldChecker = new CyberplatFieldCheck($this->_organizationIds);
    }

    /**
     * Проверка параметров действия "проверка данных платежа"
     *
     * @param array $data
     * @return AnswerOk
     */
    public function check(&$data)
    {
        $this->fieldChecker->assertType($data);
        $this->fieldChecker->assertAmount($data);
        $this->fieldChecker->assertNumber($data);

        $answer = new AnswerOk("Абонент найден");
        $answer->authcode = $this->_organizationToAuthCode[$this->fieldChecker->organizationId];

        return $answer;
    }

    /**
     * Проверка и проведение платежа
     *
     * @param array $data
     * @throws \Exception
     * @throws ModelValidationException
     * @return AnswerOkPayment
     */
    public function payment(&$data)
    {
        $this->fieldChecker->assertType($data);
        $this->fieldChecker->assertAmount($data);
        $this->fieldChecker->assertReceipt($data);

        $paymentDate = $this->fieldChecker->assertDate($data);
        $client = $this->fieldChecker->assertNumber($data);

        if ($this->fieldChecker->isReceiptAdded($data)) {
            $payment = Payment::findOne(["payment_no" => $data["receipt"]]);

            $answer = new AnswerOkPayment();
            $answer->setData([
                    "authcode" => $payment->id,
                    "date" => date(DateTimeZoneHelper::ISO8601_WITHOUT_TIMEZONE, strtotime($payment->add_date))
                ]
            );

            return $answer;
        }

        $now = new \DateTime();

        $b = Bill::dao()->getPrepayedBillOnSum($client->id, $data["amount"], Currency::RUB);

        $payment = new Payment();
        $payment->client_id = $client->id;
        $payment->bill_no = $b ? $b->bill_no : "";
        $payment->bill_vis_no = $b ? $b->bill_no : "";
        $payment->payment_no = $data["receipt"];
        $payment->oper_date = $now->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $payment->payment_date = $paymentDate;
        $payment->add_date = $now->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $payment->type = Payment::TYPE_ECASH;
        $payment->ecash_operator = Payment::ECASH_CYBERPLAT;
        $payment->sum = $data["amount"];
        $payment->currency = Currency::RUB;
        $payment->payment_rate = 1;
        $payment->original_sum = $data["amount"];
        $payment->original_currency = Currency::RUB;
        $payment->comment = "Cyberplat pay# " . $data["receipt"] . " at " . str_replace("T", " ", $data["date"]);
        if (!$payment->save()) {
            throw new ModelValidationException($payment);
        }

        EventQueue::go(EventQueue::CYBERPLAT_PAYMENT,
            ["client_id" => $client->id, "payment_id" => $payment->id]); // for start update balance

        $answer = new AnswerOkPayment();
        $answer->setData([
            "authcode" => $payment->id,
            "date" => $now->format(DateTimeZoneHelper::ISO8601_WITHOUT_TIMEZONE)
        ]);

        return $answer;
    }

    /**
     * Статус проверка и действие "статус платежа"
     *
     * @param array $data
     * @throws AnswerErrorStatus
     * @return AnswerOkPayment
     */
    public function status(&$data)
    {
        $this->fieldChecker->assertReceipt($data);

        $pay = Payment::findOne(['payment_no' => $data["receipt"]]);

        if (!$pay) {
            throw new AnswerErrorStatus();
        }

        $answer = new AnswerOkPayment();
        $answer->setData([
                "authcode" => $pay->id,
                "date" => date(DateTimeZoneHelper::ISO8601_WITHOUT_TIMEZONE, strtotime($pay->add_date))
            ]
        );

        return $answer;
    }

    /**
     * Проверка подписи запроса
     *
     * @return bool
     * @throws AnswerErrorSign
     */
    public function assertSign()
    {
        $queryStr = $_SERVER["QUERY_STRING"];

        if (!preg_match("/(action=.*)&sign=(.*)/", $queryStr, $o) || !CyberplatCrypt::me()->checkSign($o[1], $o[2])) {
            throw new AnswerErrorSign();
        }

        return true;
    }
}