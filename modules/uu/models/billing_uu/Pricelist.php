<?php

namespace app\modules\uu\models\billing_uu;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\classes\traits\GetInsertUserTrait;
use app\classes\traits\GetUpdateUserTrait;
use app\models\Currency;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use app\modules\nnp\models\PackagePricelistNnp;
use app\modules\nnp\models\PackagePricelistNnpInternet;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * ННП прайслисты v.2
 */
class Pricelist extends ActiveRecord
{
    const ID_SERVICE_TYPE_DATA = 3;

    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'billing_uu.pricelist';
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
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param int $serviceTypeId
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $serviceTypeId = null
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = ($serviceTypeId ? ['service_type_id' => $serviceTypeId] : [])
        );
    }
}