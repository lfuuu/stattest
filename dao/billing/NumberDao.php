<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\Number;
use yii\helpers\ArrayHelper;

/**
 * @method static NumberDao me($args = null)
 * @property
 */
class NumberDao extends Singleton
{

    public function getList($typeId = false)
    {
        $query = Number::find();
        if ($typeId !== false) {
            $query->andWhere(['type_id' => $typeId]);
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
