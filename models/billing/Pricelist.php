<?php
namespace app\models\billing;

use app\dao\billing\PricelistDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class Pricelist extends ActiveRecord
{
    public static function tableName()
    {
        return 'voip.pricelist';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return PricelistDao::me();
    }
}