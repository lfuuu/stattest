<?php

namespace app\classes\documents;

use app\models\Currency;
use app\models\Language;

class BillDocRepEnEUR extends BillDocRepEnUSD
{

    public function getCurrency()
    {
        return Currency::EUR;
    }

    public function getName()
    {
        return 'Счет (предоплата) EUR';
    }
}