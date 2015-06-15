<?php

namespace app\classes\documents;

class BillDocRepRuRUB extends DocumentReport
{

    public function getCountryLang()
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

    public function getDocSource()
    {
        return 2;
    }

    public function getName()
    {
        return 'Счет (предоплата) alfa';
    }

}