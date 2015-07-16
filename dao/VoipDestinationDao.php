<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\voip\Destination;
use yii\helpers\ArrayHelper;

/**
 * @method static VoipDestinationDao me($args = null)
 * @property
 */
class VoipDestinationDao extends Singleton
{

    public function getList($withEmpty = false)
    {
        $list =
            ArrayHelper::map(
                Destination::find()
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );

        if ($withEmpty) {
            $list = ['' => '-- Направление --'] + $list;
        }

        return $list;
    }

}