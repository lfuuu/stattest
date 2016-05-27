<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Currency;
use yii\helpers\ArrayHelper;

/**
 * @method static CurrencyDao me($args = null)
 * @property
 */
class CurrencyDao extends Singleton
{

    public function getList($columnName = 'name', $isWithEmpty = false, $currencyId = false)
    {
        $query = Currency::find();

        if ($currencyId !== false) {
            $query->andWhere(['currency_id' => $currencyId]);
        }

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'id',
                $columnName
            );

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

}