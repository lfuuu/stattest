<?php
namespace app\dao\tariffication;

use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\tariffication\ServiceType;
use yii\helpers\ArrayHelper;

/**
 * @method static ServiceTypeDao me($args = null)
 * @property
 */
class ServiceTypeDao extends Singleton
{

    public function getList($withEmpty = false)
    {
        $list =
            ArrayHelper::map(
                ServiceType::find()
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
        if ($withEmpty) {
            $list = ['' => '-- Тип услуги --'] + $list;
        }
        return $list;
    }

}
