<?php

namespace app\classes\documents;

use DateTime;
use app\models\Currency;

class BillDocRepRuRUB extends DocumentReport
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
        return 'Счет (предоплата)';
    }

    protected function postFilterLines()
    {
        $now = (new DateTime())->format('Ym');

        foreach ($this->lines as &$line) {
            if(
                $now <= (new DateTime($line['date_from']))->format('Ym')
                && preg_match('/^\s*Абонентская\s+плата|^\s*Поддержка\s+почтового\s+ящика|^\s*Виртуальная\s+АТС/', $line['item'])
            ){
                $line['item'] = str_replace('Абонентская', 'абонентскую', str_replace('плата', 'плату', $line['item']));
                $line['item'] = str_replace('Поддержка', 'поддержку', $line['item']);
                $line['item'] = str_replace('Виртуальная', 'виртуальную', $line['item']);
                $line['item'] = 'Авансовый платеж за ' . $line['item'];
            }
        }

        return parent::postFilterLines();
    }

}