<?php


class m160224_132800_rename_currency_rate extends \app\classes\Migration
{
    /**
     * Накатить
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE bill_currency_rate RENAME currency_rate');
    }

    /**
     * Откатить
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE currency_rate RENAME bill_currency_rate');
    }
}
