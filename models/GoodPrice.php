<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class GoodPrice
 *
 * @property string good_id
 * @property string descr_id
 * @property string price_type_id
 * @property float price
 * @property string currency
 */
class GoodPrice extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'g_good_price';
    }
}
