<?php

namespace app\classes\documents;

use app\models\Currency;
use app\models\Language;

class BillDocRepEnUSD extends DocumentReport
{
    public $isAllLanguages = true;

    public function getLanguage()
    {
        return Language::LANGUAGE_ENGLISH;
    }

    public function getCurrency()
    {
        return Currency::USD;
    }

    public function getDocType()
    {
        return self::DOC_TYPE_BILL;
    }

    public function getName()
    {
        return 'Счет (предоплата) USD';
    }

}