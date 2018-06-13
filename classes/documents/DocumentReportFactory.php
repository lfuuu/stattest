<?php

namespace app\classes\documents;

use Yii;
use app\classes\Singleton;
use app\classes\Assert;
use app\models\Bill;

/**
 * @method static DocumentReportFactory me($args = null)
 */
class DocumentReportFactory extends Singleton
{

    /**
     * @return array
     */
    private static function getDocTypes()
    {
        return [
            BillDocRepRuRUB::className(),
            BillDocRepHuHUF::className(),
            BillDocRepEnUSD::className(),
            BillDocRepEnEUR::className(),
            DocNoticeMCMTelekom::className(),
            DocSoglMCMTelekom::className(),
            DocSoglMCNTelekom::className(),
            ProformaDocument::className(),
            CreditNoteDocument::className(),
            InvoiceDocument::className(),
            BillOperator::className(),
        ];
    }

    /**
     * @param Bill $bill
     * @param bool|false $docType
     * @return DocumentReport[]
     */
    public function availableDocuments(Bill $bill, $docType = null)
    {
        $currency = $bill->currency;
        $language = $bill->clientAccount->contragent->country->lang;

        return self::availableDocumentsEx($language, $currency, $docType);
    }

    /**
     * @param bool|false $language
     * @param bool|false $currency
     * @param bool|false $docType
     * @return DocumentReport[]
     */
    public function availableDocumentsEx($language = null, $currency = null, $docType = null)
    {
        $result = [];

        if (!is_array($docType)) {
            $docType = [$docType];
        }

        foreach (self::getDocTypes() as $documentClass) {
            /** @var DocumentReport $documentReport */
            $documentReport = new $documentClass;

            if ($docType !== null && !in_array($documentReport->getDocType(), $docType)) {
                continue;
            }

            if (!$documentReport->isAllLanguages && $language !== null && $documentReport->getLanguage() != $language) {
                continue;
            }

            if (!$documentReport->isMultiCurrencyDocument && $currency !== null && $documentReport->getCurrency() != $currency) {
                continue;
            }

            $result[] = $documentReport;
        }

        return $result;
    }

    /**
     * @param Bill $bill
     * @param string|array $docType
     * @param bool|false $sendEmail
     * @return DocumentReport
     * @throws \yii\base\Exception
     */
    public function getReport(Bill $bill, $docType, $sendEmail = false)
    {
        foreach (self::availableDocuments($bill, $docType) as $documentReport) {
            return
                $documentReport
                    ->setSendEmail($sendEmail)
                    ->setBill($bill)
                    ->prepare();
        }

        Assert::isUnreachable('Document report not found');
    }


}
