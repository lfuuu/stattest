<?php

namespace app\classes\documents;

use app\models\Currency;

class BillDocRepHuHUF extends DocumentReport
{

    public function getLanguage()
    {
        return 'hu';
    }

    public function getCurrency()
    {
        return Currency::HUF;
    }

    public function getDocType()
    {
        return self::BILL_DOC_TYPE;
    }

    public function getName()
    {
        return 'Счет (предоплата)';
        //return 'Díjbekérő';
    }

}