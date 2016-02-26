<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\TariffCallChat;
use yii\helpers\ArrayHelper;

/**
 * @method static CountryDao me($args = null)
 * @property
 */
class TariffCallChatDao extends Singleton
{

    public function getList($withEmpty = false)
    {
        $list =
            ArrayHelper::map(
                TariffCallChat::find()
                    ->orderBy('description')
                    ->asArray()
                    ->all(),
                'id',
                'description'
            );
        if ($withEmpty) {
            $list = ['' => '-- Тариф --'] + $list;
        }

        return $list;
    }
}