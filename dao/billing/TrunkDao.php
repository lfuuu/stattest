<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\Trunk;
use yii\helpers\ArrayHelper;

/**
 * @method static TrunkDao me($args = null)
 * @property
 */
class TrunkDao extends Singleton
{

    public function getList($serverId = false)
    {
        $query = Trunk::find();

        if ($serverId !== false) {
            $query->andWhere(['server_id' => $serverId]);
        }

        $query->andWhere('show_in_stat = true');

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
