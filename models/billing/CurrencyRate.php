<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property string $date
 * @property string $currency
 * @property float $rate
 */
class CurrencyRate extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.currency_rate';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

}
