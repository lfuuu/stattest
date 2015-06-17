<?php

namespace app\classes\documents;

class BillDocRepRuRUB extends DocumentReport
{

    public function getLanguage()
    {
        return 'ru';
    }

    public function getCurrency()
    {
        return self::CURRENCY_RUB;
    }

    public function getDocType()
    {
        return self::BILL_DOC_TYPE;
    }

    public function getName()
    {
        return 'Счет (предоплата)';
    }

}