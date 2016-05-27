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

    public function getList($currencyId, $isWithEmpty = false)
    {
        $list =
            ArrayHelper::map(
                TariffCallChat::find()
                    ->orderBy('description')
                    ->andWhere(['currency_id' => $currencyId])
                    ->asArray()
                    ->all(),
                'id',
                'description'
            );
        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }
}