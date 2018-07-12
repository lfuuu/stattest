<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $bill_id
 * @property int $line_pk
 * @property string $created_at
 * @property float $once
 * @property float $percentage_once
 * @property float $percentage_of_fee
 * @property float $percentage_of_over
 * @property float $percentage_of_margin
 */
class PartnerRewards extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'partner_rewards';
    }

    /**
     * Вознаграждение не должно формироваться, если поля "Разовое", "% от подключения","% от абонентской платы",
     * "% от превышения","% от маржи" является null или 0.00
     *
     * @return bool
     */
    public function isNullable()
    {
        return !(
            $this->once > 0 ||
            $this->percentage_once > 0 ||
            $this->percentage_of_fee > 0 ||
            $this->percentage_of_over > 0 ||
            $this->percentage_of_margin > 0
        );
    }
}