<?php
namespace app\dao;

use app\classes\Singleton;
use app\classes\traits\GetListTrait;
use app\models\City;
use app\models\UsageVoip;
use Yii;
use yii\helpers\ArrayHelper;

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
     * @param bool $isWithClosed
     * @return array
     */
    public function getList($isWithEmpty = false, $countryId = null, $isWithClosed = false)
    {
        $query = City::find();
        if ($countryId) {
            $query->andWhere(['country_id' => $countryId]);
        }

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );

        if ($isWithClosed) {
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
     * @param bool $isWithClosed
     * @return array
     */
    public function getListWithCountries($isWithEmpty = false, $isWithClosed = false)
    {
        $list = [];

        if ($isWithClosed) {
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
                ->orderBy('name')
                ->all();

        foreach ($cities as $city) {
            $list[$city->id] = $city->name . ' / ' . $city->country->name;
        }

        return $list;
    }
}