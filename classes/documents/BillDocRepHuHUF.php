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
        if (count($this->lines) == 1 && $this->lines[0]['type'] == 'zadatok') {
            return 'payment';
        }
        else {
            return self::BILL_DOC_TYPE;
        }
    }

    public function getName()
    {
        return 'Счет (предоплата)';
        //return 'Díjbekérő';
    }

}