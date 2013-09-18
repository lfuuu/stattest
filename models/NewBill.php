<?php
class NewBill extends ActiveRecord\Model
{
	static $table_name = 'newbills';
    static $private_key = 'bill_no';

    static $belongs_to = array(
        array('client', 'class_name' => 'ClientCard')
    );
}
