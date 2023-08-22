<?php

namespace app\classes\documents;

use app\classes\Html2Pdf;
use app\models\Currency;
use app\models\Language;
use app\modules\uu\models\Bill as uuBill;
use app\modules\uu\models_light\InvoiceLight;
use yii\base\InvalidParamException;

class InvoiceProformaDocument extends DocumentReport
{
    const DOC_TYPE_INVOICE = 'proforma';

    public $isAllLanguages = true;
    public $isMultiCurrencyDocument = true;

    /** @var InvoiceLight */
    private $_invoice = null;

    /**
     * Тип документа
     *
     * @return string
     */
    public function getDocType()
    {
        return self::DOC_TYPE_INVOICE;
    }

    /**
     * Название документа
     *
     * @return string
     */
    public function getName()
    {
        return 'UU Invoice';
    }

    /**
     * Получение контента как есть
     *
     * @param bool $inline_img
     * @return string
     */
    public function render($inline_img = true)
    {
        if ($this->_invoice) {
            return $this->_invoice->render();
        }

        $bill = $this->bill;

        if (!$bill) {
            throw new InvalidParamException();
        }

        if ($bill->uu_bill_id) {
            $bill = uuBill::findOne(['id' => $bill->uu_bill_id]);

            if (!$bill) {
                throw new InvalidParamException();
            }
        }

        $clientAccount = $bill->clientAccount;

        $this->_invoice = (new InvoiceLight($clientAccount))
            ->setBill($bill)
            ->setLanguage($clientAccount->contragent->lang_code)
            ->setInvoiceProformaBill($bill);


        return $this->_invoice->render();
    }

    /**
     * Получение контента в PDF
     *
     * @return string
     */
    public function renderAsPDF()
    {
        $generator = new Html2Pdf();
        $generator->html = $this->render();
        return $generator->pdf;
    }
}
