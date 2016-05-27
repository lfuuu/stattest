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

    public function getList($isWithEmpty = false)
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

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

}