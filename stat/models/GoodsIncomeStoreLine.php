<?php
class GoodsIncomeStoreLine extends ActiveRecord\Model
{
    static $table_name = 'g_income_store_lines';
    static $belongs_to = array(
        array('document', 'class_name' => 'GoodsIncomeStore'),
        array('good', 'class_name' => 'Good'),
    );
}