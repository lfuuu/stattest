<?php

namespace app\classes\documents;

class BillDocRepHuFT extends DocumentReport
{

    public function getLanguage()
    {
        return 'hu';
    }

    public function getCurrency()
    {
        return self::CURRENCY_FT;
    }

    public function getDocType()
    {
        return self::BILL_DOC_TYPE;
    }

    public function getName()
    {
        return 'Díjbekérő';
    }

}