<?php
namespace app\models\billing;

use app\dao\billing\PricelistDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $type
 * @property bool $orig
 *
 * @property string $parser_settings
 * @property
 */
class Pricelist extends ActiveRecord
{
    const TYPE_CLIENT = 'client';
    const TYPE_OPERATOR = 'operator';
    const TYPE_LOCAL = 'network_prices';

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

    public function isClient()
    {
        return $this->type == self::TYPE_CLIENT;
    }

    public function isOperator()
    {
        return $this->type == self::TYPE_OPERATOR;
    }

    public function isLocal()
    {
        return $this->type == self::TYPE_LOCAL;
    }

}