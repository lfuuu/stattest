<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\Operator;
use yii\helpers\ArrayHelper;

/**
 * @method static OperatorDao me($args = null)
 * @property
 */
class OperatorDao extends Singleton
{

    public function getList($serverId = false)
    {
        $query = Operator::find();
        if ($serverId !== false) {
            $query->andWhere(['region' => $serverId]);
        }

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
