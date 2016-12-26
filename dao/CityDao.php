<?php
namespace app\dao;

use Yii;
use yii\helpers\ArrayHelper;
use app\classes\Singleton;
use app\classes\traits\GetListTrait;
use app\models\City;
use app\models\Number;

/**
 * @method static CityDao me($args = null)
 * @property
 */
class CityDao extends Singleton
{
    /**
     * Вернуть список городов
     *
     * @param bool $isWithEmpty
     * @param null $countryId
     * @param bool $isWithNullAndNotNull
     * @param bool $isUsedOnly
     * @return array
     */
    public function getList($isWithEmpty = false, $countryId = null, $isWithNullAndNotNull = false, $isUsedOnly = true)
    {
        $query = City::find();
        if ($countryId) {
            $query->andWhere(['country_id' => $countryId]);
        }

        if ($isUsedOnly) {
            $query->andWhere(['in_use' => 1]);
        }

        $query->orderBy([
            'order' => SORT_ASC,
            'name' => SORT_ASC,
        ]);

        $list = $query
            ->indexBy('id')
            ->all();

        if ($isWithNullAndNotNull) {
            $list = [
                    GetListTrait::$isNull => '- ' . Yii::t('common', 'Is empty') . ' -',
                    GetListTrait::$isNotNull => '- ' . Yii::t('common', 'Is not empty') . ' -',
                ] + $list;
        }

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

    /**
     * Вернуть список городов с добавлением страны
     *
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @return array
     */
    public function getListWithCountries($isWithEmpty = false, $isWithNullAndNotNull = false)
    {
        $list = [];

        if ($isWithNullAndNotNull) {
            $list = [
                    GetListTrait::$isNull => '- ' . Yii::t('common', 'Is empty') . ' -',
                    GetListTrait::$isNotNull => '- ' . Yii::t('common', 'Is not empty') . ' -',
                ] + $list;
        }

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        $cities =
            City::find()
                ->joinWith('country')
                ->orderBy(['order' => SORT_ASC])
                ->all();

        foreach ($cities as $city) {
            $list[$city->id] = $city->name . ' / ' . $city->country->name;
        }

        return $list;
    }

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
}