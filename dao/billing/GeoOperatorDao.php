<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\GeoOperator;
use yii\helpers\ArrayHelper;

/**
 * @method static GeoOperatorDao me($args = null)
 * @property
 */
class GeoOperatorDao extends Singleton
{

    public function getList()
    {
        $query = GeoOperator::find();

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
