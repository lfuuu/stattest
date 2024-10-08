<?php

use app\models\Invoice;
use app\models\Bill;
use app\models\UserRight;

/**
 * Class m241004_141716_invoice_date
 */
class m241004_141716_invoice_date extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Invoice::tablename(), 'invoice_date', 'DATE NULL');
        $this->addColumn(Bill::tablename(), 'invoice_date', 'DATE NULL');
        $this->update(UserRight::tableName(), ['values' => 'read,edit,delete,admin,del_docs,edit_ext,invoice_date', 'values_desc' => 'просмотр,изменение,удаление,изменение счета в любое время,Удаление отсканированных актов,Редактирование номера внешнего счета,Изменение даты счёт-фактуры'], ['resource' => 'newaccounts_bills']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Invoice::tablename(), 'invoice_date');
        $this->dropColumn(Bill::tablename(), 'invoice_date');
        $this->update(UserRight::tableName(), ['values' => 'read,edit,delete,admin,del_docs,edit_ext', 'values_desc' => 'просмотр,изменение,удаление,изменение счета в любое время,Удаление отсканированных актов,Редактирование номера внешнего счета'], ['resource' => 'newaccounts_bills']);
    }
}
