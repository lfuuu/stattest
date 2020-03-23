<?php

namespace app\modules\uu\models\billing_uu;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\Expression;

/**
 * ННП прайслисты v.2
 */
class PricelistFilterB extends ActiveRecord
{
    public static function tableName()
    {
        return 'billing_uu.pricelist_filter_b';
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixPriceBasic()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['pricelist_filter_b_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixPrice()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['pricelist_filter_b_id' => 'id'])
            ->select(['*', 'b_number_price' => new Expression('round(billing_uu.pricelist_prefix_price.b_number_price, 6)')])
            ->innerJoin('(select *, row_number() over (partition by pricelist_filter_b_id order by prefix_b) as rownum 
                from billing_uu.pricelist_prefix_price p
                ) p1', 'p1.id = billing_uu.pricelist_prefix_price.id')
            ->where('p1.rownum <= :limit')
            ->orderBy('billing_uu.pricelist_prefix_price.prefix_b')
            ->addParams([':limit' => PricelistPrefixPrice::PAGE_LIMIT]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrefixPriceNoLimit()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['pricelist_filter_b_id' => 'id'])
            ->select([
                'billing_uu.pricelist_prefix_price.*',
                'b_number_price' => new Expression('round(billing_uu.pricelist_prefix_price.b_number_price, 6)')
            ])
            ->orderBy('billing_uu.pricelist_prefix_price.prefix_b, billing_uu.pricelist_prefix_price.date_from');
    }

    public function getPrefixPriceCount()
    {
        return $this->hasMany(PricelistPrefixPrice::className(), ['pricelist_filter_b_id' => 'id'])
            ->select(['pricelist_filter_b_id', 'total_count' => new Expression('count(*)')])
            ->groupBy('pricelist_filter_b_id');
    }
}