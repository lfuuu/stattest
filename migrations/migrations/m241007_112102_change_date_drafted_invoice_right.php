<?php

/**
 * Class m241007_112102_change_date_drafted_invoice_right
 */
class m241007_112102_change_date_drafted_invoice_right extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update('user_rights', ['values' => 'read,edit,delete,admin,del_docs,edit_ext,invoice_date', 'values_desc' => 'просмотр,изменение,удаление,изменение счета в любое время,Удаление отсканированных актов,Редактирование номера внешнего счета,Изменение даты счёт-фактуры'], ['resource' => 'newaccounts_bills']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update('user_rights', ['values' => 'read,edit,delete,admin,del_docs,edit_ext', 'values_desc' => 'просмотр,изменение,удаление,изменение счета в любое время,Удаление отсканированных актов,Редактирование номера внешнего счета'], ['resource' => 'newaccounts_bills']);
    }
}
