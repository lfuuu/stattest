<?php

class NewBill extends ActiveRecord\Model
{
    static $table_name = "newbills";

    public function getLastUnpayedBill($clientId)
    {
        return NewBill::find('first', array(
                    "conditions" => array("client_id = ? and is_payed in (0,2)", $clientId), // 0 - not paid, 1 - fully paid, 2 - partly paid
                    "limit" => 1,
                    "order" => "bill_date desc"
                    )
        );
    }
}
