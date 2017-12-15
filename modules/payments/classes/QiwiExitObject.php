<?php

namespace app\modules\payments\classes;

class QiwiExitObject extends \yii\base\Exception
{
    private $_data = [];

    const OK = 0;
    const ERROR_TEMPORARY = 1;
    const ERROR_ACCOUNT_FORMAT = 4;
    const ERROR_ACCOUNT_NOT_FOUND = 5;
    const ERROR_ACCOUNT_NOT_PAYABLE = 7;
    const ERROR_SERVICE_ERROR = 8;
    const ERROR_ACCOUNT_BLOCKED = 79;
    const ERROR_PAYMENT_IN_PROCESS = 90;
    const ERROR_SUM_SMALL = 241;
    const ERROR_SUM_BIG = 242;
    const ERROR_ACCOUNT_CHECK_ERROR = 243;
    const ERROR_UNKNOWN = 300;

    const MESSAGE_BY_CODE = [
        self::OK => 'OK',
        self::ERROR_TEMPORARY => 'Temporary error. Please try again later.',
        self::ERROR_ACCOUNT_FORMAT => 'Invalid subscriber ID format',
        self::ERROR_ACCOUNT_NOT_FOUND => 'Caller ID not found (Wrong number)',
        self::ERROR_ACCOUNT_NOT_PAYABLE => 'Acceptance of payment is forbidden by the supplier',
        self::ERROR_SERVICE_ERROR => 'Service error',
        self::ERROR_ACCOUNT_BLOCKED => 'Subscriber account is not active',
        self::ERROR_PAYMENT_IN_PROCESS => 'Payment is not completed',
        self::ERROR_SUM_SMALL => 'Amount is too small',
        self::ERROR_SUM_BIG => 'Amount is too high',
        self::ERROR_ACCOUNT_CHECK_ERROR => 'Can not check account status',
        self::ERROR_UNKNOWN => 'Unknown error'
    ];

    /**
     * QiwiExitObject constructor.
     *
     * @param integer $exitCode
     * @param string $message
     * @param array $data
     * @internal param string $comment
     */
    public function __construct($exitCode, $message = '', $data = [])
    {
        if (!array_key_exists($exitCode, self::MESSAGE_BY_CODE)) {
            $exitCode = self::ERROR_UNKNOWN;
            !$message && $message = 'Unknown code ' . $exitCode;
        }

        $this->code = $exitCode;
        $this->message = ($message ?: self::MESSAGE_BY_CODE[$exitCode]);
        $exitCode == self::OK && $data && ($this->_data = $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

}