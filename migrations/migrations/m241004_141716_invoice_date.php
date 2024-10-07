<?php

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
        $this->addColumn('invoice', 'invoice_date', 'DATE NULL');
        $this->addColumn('newbills', 'invoice_date', 'DATE NULL');
        $this->update('user_rights', ['values' => 'read,edit,delete,admin,del_docs,edit_ext,invoice_date', 'values_desc' => 'просмотр,изменение,удаление,изменение счета в любое время,Удаление отсканированных актов,Редактирование номера внешнего счета,Изменение даты счёт-фактуры'], ['resource' => 'newaccounts_bills']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn('invoice', 'invoice_date');
        $this->dropColumn('newbills', 'invoice_date');
        $this->update('user_rights', ['values' => 'read,edit,delete,admin,del_docs,edit_ext', 'values_desc' => 'просмотр,изменение,удаление,изменение счета в любое время,Удаление отсканированных актов,Редактирование номера внешнего счета'], ['resource' => 'newaccounts_bills']);
    }
}
