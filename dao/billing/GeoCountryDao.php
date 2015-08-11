<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\GeoCountry;
use yii\helpers\ArrayHelper;

/**
 * @method static GeoCountryDao me($args = null)
 * @property
 */
class GeoCountryDao extends Singleton
{

    public function getList()
    {
        $query = GeoCountry::find();

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