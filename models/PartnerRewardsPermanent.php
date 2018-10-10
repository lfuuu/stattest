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
 * @property int $partner_id
 */
class PartnerRewardsPermanent extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'partner_rewards_permanent';
    }
}