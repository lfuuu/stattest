<?php

namespace app\dao;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ActualNumber;
use app\models\Param;
use app\models\PaymentSberOnline;
use Yii;
use app\classes\Singleton;
use DateTime;
use DateTimeZone;

/**
 * @method static PaymentSberOnlineDao me($args = null)
 */
class PaymentSberOnlineDao extends Singleton
{
    CONST TYPE_TEL = 'TEL';
    CONST TYPE_LS = 'LS';

    /**
     * Обнаружение архива с платежами Сбербанк Online
     *
     * @param string $filePath
     * @return bool
     */
    public function detectPaymentArchive($filePath)
    {
        $zip = new \ZipArchive;

        $zipOpenResult = $zip->open($filePath);
        if (!$zipOpenResult || $zipOpenResult != \ZipArchive::ER_OK) {
            return false;
        }

        $result = false;
        if ($zip->numFiles) {
            $result = true;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileInfo = $zip->statIndex($i);

                if (
                    $fileInfo['size'] == 0
                    || !preg_match('/[0-9]{10}_[0-9]{20}_[0-9}{3}.y[0-9]{2,}/', $fileInfo['name'])
                ) {
                    $result = false;
                    break;
                }

                if (!self::detectPaymentList($zip->getFromIndex($i))) {
                    $result = false;
                    break;
                }
            }
        }

        $zip->close();

        return $result;
    }

    /**
     * Загрузка платежей из архива
     *
     * @param string $filePath
     * @throws \Exception
     */
    public function loadPaymentsFromArchive($filePath)
    {
        $zip = new \ZipArchive;
        if ($zip->open($filePath) === true) {
            Yii::$app->db->transaction(function ($db) use ($zip) {
                $totalSum = $count = 0;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileContent = str_replace("\r", "", $zip->getFromIndex($i));

                    foreach (str_getcsv($fileContent, "\n") as $rowStr) {
                        $row = str_getcsv($rowStr, ';');
                        $sum = self::savePaymentRow($row);
                        if ($sum) {
                            $totalSum += $sum;
                            $count++;
                        }
                    }
                }

                self::savePaymentsInfo($count, $totalSum);
            });
            $zip->close();
        } else {
            throw new \Exception('Ошибка открытия архива');
        }
    }

    /**
     * Проверяем, является для файл - выгрузкой Сбербанка Online
     *
     * @param string $header
     * @return boolean
     */
    public function detectPaymentList($header)
    {
        //29-06-2016;17-54-16;9055
        //29-06-2016;10-52-20;7981
        return preg_match("/^[0123][0-9]-[0-9]{2}-20[0-9]{2};[0-9]{2}-[0-9]{2}-[0-9]{2};[0-9]+/", $header);
    }

    public function detectPaymentListType($fileInfo)
    {
        if (!preg_match('/^(LS|TEL)_[0-9]{10}_[0-9]{20}_[0-9}{3}.y[0-9]{2,}/', $fileInfo['name'], $m)) {
            return false;
        }

        switch ($m[1]) {
            case self::TYPE_TEL:
                return self::TYPE_TEL;
                break;
            case self::TYPE_LS:
                return self::TYPE_LS;
                break;
            default:
                throw new \LogicException('Неизвестный тип документа');
        }

    }

    /**
     * Загружаем платежи из файла
     *
     * @param string $fileName
     * @param string $type
     */
    public function loadPaymentListFromFile($fileName, $type = null)
    {
        $fileResource = fopen($fileName, "rb");
        Yii::$app->db->transaction(function ($db) use ($fileResource, $type) {
            while ($row = fgetcsv($fileResource, null, ';')) {
                self::savePaymentRow($row, $type);
            }
        });
        fclose($fileResource);
    }

    /**
     * Сохраняем платеж в базу
     *
     * @param array $row
     * @param null $type
     * @return bool|int|null
     * @throws \Exception
     */
    private static function savePaymentRow($row, $type = null)
    {
        if ($row[0][0] == '=') { //total flag
            return null;
        }

        if ($type == self::TYPE_TEL) {
            return self::_saveTelPaymentRow($row);
        } elseif ($type == self::TYPE_LS) {
            return self::_saveLsPaymentRow($row);
        } elseif ($type) {
            throw new \LogicException('Ошибка сохранения');
        }

        $paymentSentDate = DateTime::createFromFormat('d-m-Y', $row[0]);
        $paymentSentDate->setTime(0, 0, 0);
        if (!$paymentSentDate) {
            throw new \Exception('Ошибка распознавания даты отправки платежа');
        }

        $now = new DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));

        $paymentReceivedDate = isset($row[14]) ? DateTime::createFromFormat('d-m-Y', $row[14]) : $now;
        $paymentReceivedDate->setTime(0, 0, 0);
        if (!$paymentReceivedDate) {
            throw new \Exception('Ошибка распознавания даты поступления платежа');
        }

        $payment = new PaymentSberOnline;
        $payment->payment_sent_date = $paymentSentDate->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $payment->payment_received_date = $paymentReceivedDate->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $payment->code1 = trim($row[1]);
        $payment->code2 = trim($row[2]);
        $payment->code3 = trim($row[3]);
        $payment->code4 = trim($row[4]);
        if (isset($row[13])) {
            $payment->code5 = trim($row[13]);
        }
        $payment->payer = iconv('CP1251', 'UTF-8', $row[5]);
        $payment->description = iconv('CP1251', 'UTF-8', $row[6]);
        $payment->sum_paid = str_replace(',', '.', $row[7]);
        $payment->sum_received = str_replace(',', '.', $row[8]);
        $payment->sum_fee = str_replace(',', '.', $row[9]);
        $payment->day = $now->format('d');
        $payment->month = $now->format('m');
        $payment->year = $now->format('Y');

        if (!$payment->isSaved()) {
            $payment->save();
            return $payment->sum_paid;
        }
        return false;
    }

    public static function _saveTelPaymentRow($row)
    {
        $now = new DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));

        $paymentSentDate = DateTime::createFromFormat('d-m-Y', $row[0]);
        $paymentSentDate->setTime(0, 0, 0);
        if (!$paymentSentDate) {
            throw new \Exception('Ошибка распознавания даты отправки платежа');
        }

        $payment = new PaymentSberOnline;

        $payment->payment_sent_date = $payment->payment_received_date = $paymentSentDate->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $payment->code1 = trim($row[1]);
        $payment->code2 = trim($row[2]);
        $payment->code3 = trim($row[3]);
        $payment->code4 = trim($row[4]);

        $payment->sum_paid = str_replace(',', '.', $row[12]);
        $payment->sum_received = str_replace(',', '.', $row[13]);
        $payment->sum_fee = str_replace(',', '.', $row[14]);

        $payment->day = $now->format('d');
        $payment->month = $now->format('m');
        $payment->year = $now->format('Y');

        $numberStr = '7' . $row[5];
        $payment->description = 'Оплата по номеру телефона: ' . $numberStr;

        if ($number = ActualNumber::findOne(['number' => $numberStr])) {
            $payment->description .= ', ЛС: ' . $number->client_id;
        }

        if (!$payment->isSaved()) {
            if (!$payment->save()) {
                throw new ModelValidationException($payment);
            }

            return $payment->sum_paid;
        }
        return false;
    }

    public static function _saveLsPaymentRow($row)
    {
        $now = new DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW));

        $paymentSentDate = DateTime::createFromFormat('d.m.Y', $row[0]);
        $paymentSentDate->setTime(0, 0, 0);
        if (!$paymentSentDate) {
            throw new \Exception('Ошибка распознавания даты отправки платежа');
        }

        $payment = new PaymentSberOnline;

        $payment->payment_sent_date = $payment->payment_received_date = $paymentSentDate->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $payment->code1 = trim($row[1]);
        $payment->code2 = trim($row[2]);
        $payment->code3 = trim($row[3]);
        $payment->code4 = trim($row[4]);

        $payment->sum_paid = str_replace(',', '.', $row[10]);
        $payment->sum_received = str_replace(',', '.', $row[11]);
        $payment->sum_fee = str_replace(',', '.', $row[12]);

        $payment->day = $now->format('d');
        $payment->month = $now->format('m');
        $payment->year = $now->format('Y');

        $payment->description = 'Оплата по ЛС: ' . $row[5];

        if (!$payment->isSaved()) {
            if (!$payment->save()) {
                throw new ModelValidationException($payment);
            }

            return $payment->sum_paid;
        }
        return false;
    }


    /**
     * Сохранение суммароной информации для последующего отображения
     *
     * @param $count
     * @param $sum
     */
    private static function savePaymentsInfo($count, $sum)
    {
        $info = [
            "Сбербанк Online. На " . mdate("d месяца Y") . " найдено платежей: " . $count .
            "шт., на сумму " . number_format($sum, 2, ".", "`")
        ];

        Param::setParam(Param::PI_LIST_LAST_INFO, $info);
    }
}
