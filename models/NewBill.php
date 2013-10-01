<?php
class NewBill extends ActiveRecord\Model
{
	static $table_name = 'newbills';
    static $private_key = 'bill_no';

    static $belongs_to = array(
        array('client', 'class_name' => 'ClientCard')
    );

    static $has_one = array(
        array('trouble', 'class_name' => 'Trouble', 'foreign_key' => 'bill_no')
        );
}
