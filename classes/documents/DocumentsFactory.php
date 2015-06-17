<?php

namespace app\classes\documents;

use app\classes\Singleton;
use Yii;
use app\classes\Assert;
use app\models\Bill;

/**
 * @method static DocumentsFactory me($args = null)
 * @property
 */
class DocumentsFactory extends Singleton
{

    private static function getDocTypes()
    {
        return [
            BillDocRepRuRUB::className(),
            BillDocRepHuRUB::className()
        ];
    }

    /**
     * @return DocumentReport[]
     */
    public function availableDocuments(Bill $bill, $docType = null)
    {
        $currency = $bill->currency;
        $language = $bill->clientAccount->contragent->country->lang;

        return self::availableDocumentsEx($language, $currency, $docType);
    }

    /**
     * @return DocumentReport[]
     */
    public function availableDocumentsEx($language = null, $currency = null, $docType = null)
    {
        $result = [];

        foreach (self::getDocTypes() as $documentClass) {
            $documentReport = new $documentClass;

            if ($docType !== null && $documentReport->getDocType() != $docType) continue;
            if ($language !== null && $documentReport->getLanguage() != $language) continue;
            if ($currency !== null && $documentReport->getCurrency() != $currency) continue;

            $result[] = $documentReport;
        }

        return $result;
    }

    /**
     * @return DocumentReport
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