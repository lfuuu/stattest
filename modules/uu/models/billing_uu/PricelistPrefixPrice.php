<?php

namespace app\modules\uu\models\billing_uu;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\Expression;

/**
 * ННП прайслисты v.2
 */
class PricelistPrefixPrice extends ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_prefix_price';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

}