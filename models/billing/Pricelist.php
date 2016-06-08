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
 */
class Pricelist extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

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

    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @param int $serviceTypeId
     * @return self[]
     */
    public static function getList($isWithEmpty = false, $isWithNullAndNotNull = false, $type = null, $orig = null)
    {
        $query = self::find()
            ->orderBy(self::getListOrderBy())
            ->indexBy('id');
        !is_null($type) && $query->andWhere(['type' => $type]);
        !is_null($orig) && $query->andWhere(['orig' => $orig]);
        $list = $query->all();

        return self::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

}