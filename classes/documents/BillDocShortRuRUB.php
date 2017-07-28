<?php

namespace app\classes\documents;

/**
 * Class BillDocShortRuRUB
 */
class BillDocShortRuRUB extends BillDocRepRuRUB
{
    /**
     * @return string
     */
    public function getDocType()
    {
        return self::DOC_TYPE_SHORTBILL;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Счет короткий';
    }

    /**
     * @return string
     */
    public function templateDocType()
    {
        return self::DOC_TYPE_BILL;
    }

    /**
     * @return $this
     */
    protected function postFilterLines()
    {
        $billLine = [
            'item' => \Yii::t(
                'biller',
                'Communications services contract #{contract_number}',
                ['contract_number' => $this->bill->clientAccount->contract->number],
                $this->getLanguage()
            ),
            'amount' => 1,
        ];

        foreach ($this->lines as $line) {
            $billLine['sum'] += $line['sum'];
            $billLine['sum_tax'] += $line['sum_tax'];
            $billLine['sum_without_tax'] += $line['sum_without_tax'];
        }

        $this->lines = [$billLine];

        return $this;
    }
}