<?php

namespace app\dao;

use app\classes\Singleton;
use app\classes\traits\GetListTrait;
use app\models\City;
use app\models\Number;

/**
 * @method static CityDao me($args = null)
 */
class CityDao extends Singleton
{
    /**
     * Обновляем список городов, доступных для использования в ЛК и услугах телефонии
     *
     * @throws \yii\db\Exception
     */
    public function markUseCities()
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();
        City::updateAll(['in_use' => 0]);
        City::updateAll(['in_use' => 1], ['id' => Number::find()->distinct()->select('city_id')->column()]);
        $transaction->commit();
    }

    /**
     * @param bool $isWithEmpty
     * @return string[]
     */
    public function getIsShowInLkList($isWithEmpty = false)
    {
        $list = [
            City::IS_SHOW_IN_LK_NONE => 'Нет',
            City::IS_SHOW_IN_LK_API_ONLY => 'API',
            City::IS_SHOW_IN_LK_FULL => 'API и ЛК',
        ];

        return GetListTrait::getEmptyList($isWithEmpty) + $list;
    }
}