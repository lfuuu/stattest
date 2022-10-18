<?php

namespace app\modules\uu\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;

/**
 * @property int $client_account_id
 * @property int $account_tariff_id
 * @property int $price
 */
class Estimation extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_estimation';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['client_account_id', 'account_tariff_id'], 'integer'],
            [['price'], 'number'],
        ];
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
                'CreatedAt' => CreatedAt::class,
        ]);

    }
}
