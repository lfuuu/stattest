<?php

namespace app\classes\documents;

use Yii;
use app\classes\Singleton;
use app\classes\Assert;
use app\models\Bill;

/**
 * @method static DocumentReportFactory me($args = null)
 * @property
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
            DocNoticeMCMTelekom::className(),
            DocSoglMCMTelekom::className(),
            DocSoglMCNTelekom::className(),
        ];
    }

    /**
     * @param Bill $bill
     * @param bool|false $docType
     * @return DocumentReport[]
     */
    public function availableDocuments(Bill $bill, $docType = false)
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
    public function availableDocumentsEx($language = false, $currency = false, $docType = false)
    {
        $result = [];

        foreach (self::getDocTypes() as $documentClass) {
            /** @var DocumentReport $documentReport */
            $documentReport = new $documentClass;

            if ($docType !== false && $documentReport->getDocType() != $docType) {
                continue;
            }
            if ($language !== false && $documentReport->getLanguage() != $language) {
                continue;
            }
            if ($currency !== false && $documentReport->getCurrency() != $currency) {
                continue;
            }

            $result[] = $documentReport;
        }

        return $result;
    }

    /**
     * @param Bill $bill
     * @param $docType
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
