<?php

namespace app\models;

use app\classes\behaviors\EventQueueAddEvent;
use app\classes\behaviors\InvoiceGeneratePdf;
use app\classes\behaviors\InvoiceNextIdx;
use app\classes\Encrypt;
use app\classes\HttpClient;
use app\classes\model\ActiveRecord;
use app\classes\Request;
use app\dao\InvoiceDao;
use app\exceptions\ModelValidationException;
use app\exceptions\web\BadRequestHttpException;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models_light\InvoiceLight;
use yii\base\InvalidCallException;
use yii\db\Expression;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @property int $id
 * @property string $number
 * @property int $organization_id
 * @property string $bill_no
 * @property int $idx
 * @property int $type_id
 * @property string $date
 * @property float $sum
 * @property float $sum_tax
 * @property float $sum_without_tax
 * @property bool $is_reversal
 * @property string $add_date
 * @property string $reversal_date
 * @property int $correction_bill_id
 * @property float $original_sum
 * @property float $original_sum_tax
 * @property int $correction_idx
 *
 * @property-read Bill $bill
 * @property-read InvoiceLine $lines
 * @property-read Organization $organization
 * @property-read Bill $correctionBill
 */
class Invoice extends ActiveRecord
{
    const TYPE_1 = 1;
    const TYPE_2 = 2;
    const TYPE_GOOD = 3;
    const TYPE_PREPAID = 4;

    const DATE_ACCOUNTING = '2018-08-01';
    const DATE_NO_RUSSIAN_ACCOUNTING = '2019-01-01';

    public static $types = [self::TYPE_1, self::TYPE_2, self::TYPE_GOOD];

    // Создается draft
    public $isSetDraft = null;

    public $isAsInsert = false;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'invoice';
    }

    /**
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'InvoiceNextIdx' => InvoiceNextIdx::class,
            'InvoiceGeneratePdf' => InvoiceGeneratePdf::class,
        ];
    }

    public static function dao()
    {
        return InvoiceDao::me();
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::class, ['bill_no' => 'bill_no']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLines()
    {
        return $this->hasMany(InvoiceLine::class, ['invoice_id' => 'id'])->orderBy(['sort' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['organization_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrectionBill()
    {
        return $this->hasOne(Bill::class, ['id' => 'correction_bill_id']);
    }

    public function getDateImmutable()
    {
        return (new \DateTimeImmutable($this->date));
    }

    /**
     * Получает даты по типу
     *
     * @param Bill $bill
     * @param int $typeId
     * @return \DateTimeImmutable
     */
    public static function getDate(Bill $bill, $typeId)
    {
        $date = new \DateTimeImmutable($bill->bill_date);

        switch ($typeId) {
            case self::TYPE_1:
                return $date->modify('last day of this month');
                break;

            case self::TYPE_2:
                return $date->modify('last day of previous month');
                break;

            case self::TYPE_GOOD:
                return self::_getBillWithGoodDate($bill, $date);
                break;

            case self::TYPE_PREPAID:
                return self::_getBillPaymentDate($bill);
                break;

            default:
                return $date;
        }
    }

    /**
     * Дата первого платежа для с/ф 4
     *
     * @param Bill $bill
     * @return bool|\DateTimeImmutable
     */
    public static function _getBillPaymentDate(Bill $bill)
    {
        /** @var Payment $payment */
        $payment = Payment::find()
            ->where(['bill_no' => $bill->bill_no])
            ->orderBy(['id' => SORT_ASC])
            ->one();

        if ($payment) {
            return (new \DateTimeImmutable($payment->payment_date));
        }

        return false;
    }

    /**
     * @param Bill $bill
     * @param $defaultDate
     * @return bool|\DateTimeImmutable
     */
    private static function _getBillWithGoodDate(Bill $bill, $defaultDate)
    {
        if (!$bill->is1C()) {
            return $defaultDate;
        }

        if ($bill->doc_date && $bill->doc_date != '0000-00-00') {
            return (new \DateTimeImmutable())->setTimestamp($bill->doc_date);
        }

        $date = self::_getShippedDateFromTrouble($bill);

        if ($date) {
            return $date;
        }

        return false;
    }

    /**
     * @param Bill $bill
     * @return bool|\DateTimeImmutable
     */
    private static function _getShippedDateFromTrouble(Bill $bill)
    {
        $value = \Yii::$app->db->createCommand("
                     SELECT 
                        min(cast(date_start AS DATE))
                     FROM 
                        tt_troubles t , `tt_stages` s  
                     WHERE 
                            t.bill_no = :bill_no
                        AND t.id = s.trouble_id 
                        AND state_id IN (SELECT id FROM tt_states WHERE state_1c = 'Отгружен')
                        ", [":bill_no" => $bill->bill_no])
            ->queryScalar();

        if ($value) {
            return (new \DateTimeImmutable($value));
        }

        return false;
    }

    /**
     * @param bool $isRevertSum
     * @throws \Exception
     */
    public function setReversal($isRevertSum = false)
    {
        $transaction = $this->db->beginTransaction();
        try {
            if ($this->is_reversal) {
                return;
            }

            $this->is_reversal = 1;
            $this->reversal_date = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))->format(DateTimeZoneHelper::DATETIME_FORMAT);

            if ($isRevertSum) {
                $this->sum = -$this->sum;
                $this->sum_without_tax = -$this->sum_without_tax;
                $this->sum_tax = -$this->sum_tax;
            }

            if ($this->correction_bill_id) {
                $this->correctionBill->isSkipCheckCorrection = true;
                $this->correctionBill->delete();
                $this->correction_bill_id = null;
            }

            if (!$this->save()) {
                throw new ModelValidationException($this);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    public static function getInfo($billNo)
    {
        $bill = Bill::findOne(['bill_no' => $billNo]);

        if (!$bill) {
            throw new NotFoundHttpException('Bill not found');
        }

        $info = [];
        foreach (array_merge(self::$types, [self::TYPE_PREPAID]) as $typeId) {
            if ($typeInfo = self::_getInfoByType($bill, $typeId)) {
                $info[$typeId] = $typeInfo;
            }
        }

        return $info;
    }

    public function _getInfoByType(Bill $bill, $typeId)
    {
        $lines = $bill->getLinesByTypeId($typeId);

        // @TODO
        // проверка - можно ли по этому счету выписать авансовую с/ф

        // нет проводок - нет документа. Кроме авансовой с/ф
        if ($typeId != self::TYPE_PREPAID && !$lines) {
            return false;
        }

        $info = [
            'status' => 'empty',
            'invoices' => [],
        ];

        /** @var Invoice $invoice */
        foreach (Invoice::find()
                     ->where(['bill_no' => $bill->bill_no, 'type_id' => $typeId])
                     ->orderBy(['id' => SORT_ASC])
                     ->all()
                 as $invoice) {
            $info['invoices'][] = $invoice;

            if ($invoice->is_reversal) {
                $info['status'] = 'reversal';
            } elseif (!$invoice->idx) {
                $info['status'] = 'draft';
            } else {
                $info['status'] = 'invoice';
                $info['stornoId'] = $invoice->id;
            }
            $info['lastId'] = $invoice->id;
        }

        return $info;
    }

    public function beforeDelete()
    {
        if ($this->correction_bill_id) {
            $correction = $this->correctionBill;
            $correction->isSkipCheckCorrection = true;
            $correction->delete();
        }

        return parent::beforeDelete();
    }

    public function afterSave($isInsert, $changedAttributes)
    {
        if (
            (!$isInsert && !$this->isAsInsert)
//            || $this->bill->clientAccount->country_id == Country::RUSSIA
            || $this->bill->bill_date < '2019-02-01'
        ) {
            return;
        }

        // строки уже есть
        if ($this->isAsInsert && $this->getLines()->count() > 0) {
            return;
        }

        foreach ($this->bill->getLinesByTypeId($this->type_id, $isInsert) as $line) {
            $newLine = new InvoiceLine();

            if ($line instanceof BillLine) {
                $data = $line->getAttributes(null, ['pk']);
            } else {
                // array
                $data = $line;
                unset($data['pk']);
            }

            $newLine->setAttributes($data, false);
            $newLine->invoice_id = $this->id;

            if ($this->is_reversal) {
                $newLine->price = -$newLine->price;
                $newLine->sum = -$newLine->sum;
                $newLine->sum_tax = -$newLine->sum_tax;
                $newLine->sum_without_tax = -$newLine->sum_without_tax;
            }

            if (!$newLine->save()) {
                throw new ModelValidationException($newLine);
            }
        }

        parent::afterSave($isInsert, $changedAttributes);
    }

    public function recalcSumCorrection()
    {
        $transaction = Invoice::getDb()->beginTransaction();
        try {
            $sums = InvoiceLine::find()
                ->where(['invoice_id' => $this->id])
                ->select([
                    'sum' => (new Expression('SUM(sum)')),
                    'sum_tax' => (new Expression('SUM(sum_tax)')),
                    'sum_without_tax' => (new Expression('SUM(sum_without_tax)')),
                ])
                ->asArray()
                ->one();

            $this->sum = $sums['sum'];
            $this->sum_tax = $sums['sum_tax'];
            $this->sum_without_tax = $sums['sum_without_tax'];

            if (!$this->save()) {
                throw new ModelValidationException($this);
            }

            $this->_makeCorrectionBill();

            ClientAccount::dao()->updateBalance($this->bill->client_id);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Создание корректировочного счета
     *
     * @throws ModelValidationException
     */
    private function _makeCorrectionBill()
    {
        $diffSum = $this->sum - $this->original_sum;
        $diffSumTax = $this->sum_tax - $this->original_sum_tax;

        if (abs($diffSum) < 0.01 && abs($diffSumTax) < 0.01) {
            if ($this->correction_bill_id) {
                $this->correctionBill->delete();
            }

            return;
        }

        if (!$this->correction_bill_id) {
            $bill = Bill::dao()->createBill($this->bill->clientAccount, $this->bill->currency);

            $bill->operation_type_id = OperationType::ID_CORRECTION;
            $bill->comment = 'Автоматическая корректировка';
            $this->correction_bill_id = $bill->id;


            $lineItem = \Yii::t(
                'biller',
                'correct_sum',
                [],
                \app\classes\Language::normalizeLang($this->bill->clientAccount->contract->contragent->lang_code)
            );

            $bill->addLine(
                $lineItem,
                1,
                $diffSum,
                BillLine::LINE_TYPE_SERVICE
            );

            $bill->sum = $diffSum;

            if (!$this->save()) {
                throw new ModelValidationException($this);
            }

            if (!$bill->save()) {
                throw new ModelValidationException($bill);
            }
        }

        $correctionBill = $this->correctionBill;

        if ($diffSum != $correctionBill->sum) {

            $line = reset($correctionBill->lines);
            $line->price = $diffSum;
            $line->calculateSum($correctionBill->price_include_vat);

            if (!$line->save()) {
                throw new ModelValidationException($line);
            }

            Bill::dao()->recalcBill($correctionBill);
        }
    }

    public function getPath()
    {
        $pathStore = realpath(\Yii::$app->basePath . '/../store/invoices');

        $organization = $this->bill->clientAccount->contract->organization->firma;
        $dateStr = (new \DateTime($this->date))->format('Y-m');

        $dirData = [$organization, $dateStr];

        $path = $pathStore;
        foreach ($dirData as $p) {
            $path .= '/' . $p;
            if (!is_dir($path)) {
                mkdir($path, 0775, true);
            }
        }

        return $path . '/';
    }

    public function getFilePath()
    {
        return $this->getPath()
            . $this->bill->client_id
            . '-' . $this->number
            . ($this->is_reversal ? 'R' : '')
            . ($this->correction_idx ? '-' . $this->correction_idx : '')
            . '.pdf';
    }

    public function downloadFile()
    {
        if (!file_exists($this->getFilePath())) {
            $this->generatePdfFile();
        }

        if (\Yii::$app instanceof \yii\console\Application) {
            throw new InvalidCallException('В консольном режиме скачать файл не возможно');
        }

        $filePath = $this->getFilePath();
        $info = pathinfo($filePath);

        \Yii::$app->response->format = Response::FORMAT_RAW;
        \Yii::$app->response->content = file_get_contents($filePath);
        \Yii::$app->response->setDownloadHeaders($info['basename'], 'application/pdf', true);

        \Yii::$app->end();

    }

    /**
     * @return bool
     */
    public function generatePdfFile()
    {
        if (!$this->bill) {
            return false;
        }

        if (file_exists($this->getFilePath())) {
            return null;
        }

        if ($this->bill->currency == Currency::HUF) {
            $pdf = $this->_getContentTemplate1Pdf();
        } else {
            $pdf = $this->_downloadPdfContent();
        }

        return file_put_contents($this->getFilePath(), $pdf);
    }

    private function _getContentTemplate1Pdf()
    {
        $invoiceDocument = (new InvoiceLight($this->bill->clientAccount))
            ->setBill($this->bill);

        $invoiceDocument->setInvoice($this);

        $langCode = $this->bill->clientAccount->contragent->lang_code;

        if (!is_null($langCode)) {
            $invoiceDocument->setLanguage($langCode);
        }

        return $invoiceDocument->render($isPdf = true);
    }

    private function _downloadPdfContent()
    {
        $data = [
            'bill' => $this->bill_no,
            'object' => 'invoice-' . $this->type_id,
            'client' => (string)$this->bill->client_id,
            'is_pdf' => '1',
            'emailed' => '1',
        ];

        $link = Encrypt::encodeArray($data);

        $req = (new HttpClient())
            ->get(\Yii::$app->params['SITE_URL'] . 'bill.php?bill=' . $link)
            ->send();

        if ($req->statusCode != 200) {
            throw new NotAcceptableHttpException($req->content, $req->statusCode);
        }

        if (strpos($req->content, '%PDF') !== 0) {
            throw new \HttpResponseException('Content is not PDF');
        }

        return $req->content;
    }
}
