<?php
namespace app\models;

use yii\db\ActiveRecord;

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

}