<?php
class GoodsIncomeDocument extends ActiveRecord\Model
{
    static $table_name = 'g_income_document';
    static $belongs_to = array(
        array('order', 'class_name' => 'GoodsIncomeOrder', 'foreign_key' => 'order_id'),
        array('client_card', 'class_name' => 'ClientCard'),
        array('organization', 'class_name' => 'GoodsOrganization'),
        array('store', 'class_name' => 'Store')
    );
    static $has_many = array(
        array('lines', 'class_name' => 'GoodsIncomeDocumentLine', 'foreign_key' => 'document_id')
    );
}