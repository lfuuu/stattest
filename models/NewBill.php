<?php

class NewBill extends ActiveRecord\Model
{
    static $table_name = "newbills";
    static $private_key = 'bill_no';

    static $belongs_to = array(
        array('client', 'class_name' => 'ClientCard')
    );

    static $has_one = array(
        array('trouble', 'class_name' => 'Trouble', 'foreign_key' => 'bill_no')
        );

    static $has_many = array(
        array("lines", "class_name" => "BillLines", "foreign_key" => "bill_no")
        );

    public function getLastUnpayedBill($clientId)
    {
        return NewBill::find('first', array(
                    "conditions" => array("client_id = ? and is_payed in (0,2)", $clientId), // 0 - not paid, 1 - fully paid, 2 - partly paid
                    "limit" => 1,
                    "order" => "bill_date desc"
                    )
        );
    }

    public function is1C()
    {
        return strpos("/", $this->bill_no) !== false;
    }
}
