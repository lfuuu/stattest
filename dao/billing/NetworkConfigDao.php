<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\NetworkConfig;
use yii\helpers\ArrayHelper;

/**
 * @method static NetworkConfigDao me($args = null)
 * @property
 */
class NetworkConfigDao extends Singleton
{

    public function getList()
    {
        $query = NetworkConfig::find();

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
