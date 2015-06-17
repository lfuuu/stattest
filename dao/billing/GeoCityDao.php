<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\GeoCity;
use yii\helpers\ArrayHelper;

/**
 * @method static GeoCityDao me($args = null)
 * @property
 */
class GeoCityDao extends Singleton
{

    public function getList()
    {
        $query = GeoCity::find();

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
