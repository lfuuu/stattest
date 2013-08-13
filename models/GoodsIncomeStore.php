<?php
class GoodsIncomeStore extends ActiveRecord\Model
{
	static $table_name = 'g_income_store';
	static $belongs_to = array(
		array('order', 'class_name' => 'GoodsIncomeOrder', 'foreign_key' => 'order_id'),
		array('store', 'class_name' => 'Store')
	);
	static $has_many = array(
		array('lines', 'class_name' => 'GoodsIncomeStoreLine', 'foreign_key' => 'document_id')
	);
}