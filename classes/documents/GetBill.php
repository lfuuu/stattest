<?php

namespace app\classes\documents;

use app\models\Currency;

class GetBill extends DocumentReport
{

    public function getLanguage()
    {
        return 'ru';
    }

    public function getCurrency()
    {
        return Currency::RUB;
    }

    public function getDocType()
    {
        return self::BILL_DOC_TYPE;
    }

    public function getName()
    {
        return 'Получение счета';
    }

}