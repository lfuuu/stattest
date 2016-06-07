<?php

namespace app\classes\documents;

use app\models\Currency;

class BillDocRepHuHUF extends DocumentReport
{

    public function getLanguage()
    {
        return 'hu-HU';
    }

    public function getCurrency()
    {
        return Currency::HUF;
    }

    public function getDocType()
    {
        return self::DOC_TYPE_BILL;
    }

    public function getName()
    {
        return 'Счет (предоплата)';
        //return 'Díjbekérő';
    }

}