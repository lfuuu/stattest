<?php
class GoodsIncomeOrder extends ActiveRecord\Model
{
    static $table_name = 'g_income_order';
    static $belongs_to = array(
        array('client_card', 'class_name' => 'ClientCard'),
        array('organization', 'class_name' => 'Organization'),
        array('store', 'class_name' => 'Store'),
        array('manager', 'class_name' => 'User', 'foreign_key' => 'manager_id'),
    );
    static $has_many = array(
        array('lines', 'class_name' => 'GoodsIncomeOrderLine', 'foreign_key' => 'order_id'),
        array('documents', 'class_name' => 'GoodsIncomeDocument', 'foreign_key' => 'order_id'),
        array('stores', 'class_name' => 'GoodsIncomeStore', 'foreign_key' => 'order_id'),
    );

    const STATUS_NOT_AGREED    = 'Не согласован';
    const STATUS_AGREED        = 'Согласован';
    const STATUS_CONFIRMED    = 'Подтвержден';
    const STATUS_ENTERING    = 'К поступлению';
    const STATUS_CLOSED        = 'Закрыт';

    public static $statuses = array(
        self::STATUS_NOT_AGREED    => 'Не согласован',
        self::STATUS_AGREED        => 'Согласован',
        self::STATUS_CONFIRMED    => 'Подтвержден',
        self::STATUS_ENTERING    => 'К поступлению',
        self::STATUS_CLOSED        => 'Закрыт',
    );

    static $before_save = array('calculate_ready');

    public function calculate_ready() {
        $this->ready =
            $this->active
            && (
                $this->status == self::STATUS_ENTERING
                || $this->status == self::STATUS_CLOSED
            );
    }
}