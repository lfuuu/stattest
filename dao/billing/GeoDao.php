<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\Geo;

/**
 * @method static GeoDao me($args = null)
 */
class GeoDao extends Singleton
{

    /**
     * @return Geo[]
     */
    public function getRegionList()
    {
        return
            Geo::find()
                ->select('region, region_name, country')
                ->distinct('region')
                ->where('region is not null')
                ->orderBy('country ASC, region ASC')
                ->asArray()
                ->all();
    }

    /**
     * @return Geo[]
     */
    public function getCitiesList()
    {
        return
            Geo::find()
                ->select('city, city_name, country, region')
                ->distinct('city')
                ->where('city is not null')
                ->orderBy('country ASC, region ASC, city ASC')
                ->asArray()
                ->all();
    }
}