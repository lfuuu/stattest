<?php

namespace app\classes\documents;

use app\models\Currency;
use app\models\Language;

class ProformaDocument extends DocumentReport
{
    const DOC_TYPE_BILL = 'proforma';

    public $isAllLanguages = true;
    public $isMultiCurrencyDocument = true;

    public function getLanguage()
    {
        return Language::LANGUAGE_ENGLISH;
    }

    public function getCurrency()
    {
        return Currency::HUF;
    }

    public function getDocType()
    {
        return self::DOC_TYPE_PROFORMA;
    }

    public function getName()
    {
        return 'PROFORMA INVOICE';
    }

}
