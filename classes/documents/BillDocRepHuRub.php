<?php

namespace app\classes\documents;

class BillDocRepHuRUB extends DocumentReport
{

    public function getLanguage()
    {
        return 'hu';
    }

    public function getCurrency()
    {
        //return self::CURRENCY_FT;
        return self::CURRENCY_RUB;
    }

    public function getDocType()
    {
        return self::BILL_DOC_TYPE;
    }

    public function getName()
    {
        return 'Счет (предоплата) HuRub (Alfa)';
    }

}