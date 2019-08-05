<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $account_id
 * @property int $year
 * @property int $month
 * @property float $balance
 */
class BalanceByMonth extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'balance_by_month';
    }
}
