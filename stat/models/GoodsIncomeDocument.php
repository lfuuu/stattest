<?php
class GoodsIncomeDocument extends ActiveRecord\Model
{
    static $table_name = 'g_income_document';
    static $belongs_to = array(
        array('order', 'class_name' => 'GoodsIncomeOrder', 'foreign_key' => 'order_id'),
        array('organization', 'class_name' => 'GoodsOrganization'),
        array('store', 'class_name' => 'Store')
    );
    static $has_many = array(
        array('lines', 'class_name' => 'GoodsIncomeDocumentLine', 'foreign_key' => 'document_id')
    );

    public function get_client_card()
    {
        return \app\models\ClientAccount::findOne($this->client_card_id);
    }
}