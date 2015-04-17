<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\Pricelist;
use yii\helpers\ArrayHelper;

/**
 * @method static PricelistDao me($args = null)
 * @property
 */
class PricelistDao extends Singleton
{

    public function getList()
    {
        $query = Pricelist::find();

        return
            ArrayHelper::map(
                $query
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
    }

}
