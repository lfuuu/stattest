<?php

namespace app\classes\documents;

use app\classes\Language;
use app\models\Currency;
use Yii;
use app\models\ClientAccount;
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
            BillDocRepRuRUB::class,
            BillDocRepHuHUF::class,
            BillDocRepEnUSD::class,
            BillDocRepEnEUR::class,
            DocNoticeMCMTelekom::class,
            DocSoglMCMTelekom::class,
            DocSoglMCNTelekom::class,
            DocSoglMCNService::class,
            DocSoglMCNTelekomToService::class,
            DocSoglMCNServiceToAbonserv::class,
            DocSoglAbonservToTelekom::class,
//            ProformaDocument::class,
            InvoiceProformaDocument::class,
            CreditNoteDocument::class,
            InvoiceDocument::class,
            BillOperator::class,
            CurrentStatementRuDocument::class,
            CurrentStatementHuDocument::class,
            CurrentStatementEnDocument::class,
            CurrentStatementDeDocument::class,
            CurrentStatementSkDocument::class,
        ];
    }

    /**
     * @param Bill|ClientAccount $bill
     * @param bool|false $docType
     * @return DocumentReport[]
     */
    public function availableDocuments($bill, $docType = null)
    {
        $currency = null;
        $language = null;

        if ($bill instanceof Bill) {
            $currency = $bill->currency;
            $language = $bill->clientAccount->clientContractModel->clientContragent->lang_code;
        } elseif ($bill instanceof ClientAccount) {
            $currency = $bill->currency;
            $language = $bill->clientContractModel->clientContragent->lang_code;
        } else {
            Assert::isUnreachable('main object unreachable');
        }

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
     * @param Bill|ClientAccount $bill
     * @param string|array $docType
     * @param bool|false $sendEmail
     * @return DocumentReport
     * @throws \yii\base\Exception
     */
    public function getReport($mainDocument, $docType, $sendEmail = false)
    {
        foreach (self::availableDocuments($mainDocument, $docType) as $documentReport) {
            $r = $documentReport->setSendEmail($sendEmail);
            $mainDocument instanceof Bill && $r->setBill($mainDocument);
            $mainDocument instanceof ClientAccount && $r->setClientAccount($mainDocument);
            return $r->prepare();
        }

        Assert::isUnreachable('Document report not found');
    }


}
