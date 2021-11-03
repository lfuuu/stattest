<?php

namespace app\dao;

use app\classes\Singleton;
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\models\Number;
use app\modules\nnp\models\NdcType;
use Yii;
use yii\db\Expression;

/**
 * @method static DidGroupDao me($args = null)
 */
class DidGroupDao extends Singleton
{
    /**
     * Вернуть список красивостей
     *
     * @param bool $isWithEmpty
     * @return string[]
     */
    public static function getBeautyLevelList($isWithEmpty = false)
    {
        $list = DidGroup::$beautyLevelNames;

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

    /**
     * Определяем DID-группу по номеру
     *
     * @param \app\models\Number $number
     * @return int
     */
    public function getIdByNumber(\app\models\Number $number)
    {
        $where = [];

        // исключения
        if (
            $number->country_code == Country::RUSSIA &&
            $number->city_id == City::MOSCOW
        ) {
            if ($number->beauty_level == DidGroup::BEAUTY_LEVEL_STANDART) {
                if (strpos($number->number, '7495') === 0) {
                    return DidGroup::ID_MOSCOW_STANDART_495;
                } elseif (strpos($number->number, '7499') === 0) {
                    return DidGroup::ID_MOSCOW_STANDART_499;
                }
            }
        }

        $query = DidGroup::find()
            ->where([ // прямое условие, без исключений
                'AND',
                [
                    'country_code' => $number->country_code,
                    'beauty_level' => $number->beauty_level,
                    'ndc_type_id' => $number->ndc_type_id,
                    'is_service' => $number->is_service,
                ],
                [
                    'OR',
                    ['city_id' => $number->city_id],
                    ['city_id' => null]
                ]
            ]);

        return $query->max('id');
    }

    /**
     * Получение списка DID-групп в стране с индексом по городам
     *
     * @param int $countryCode
     * @param int $ndcTypeId
     * @return array
     */
    public function getDidGroupsByCity($countryCode, $ndcTypeId = NdcType::ID_GEOGRAPHIC)
    {
        $didGroupQuery = DidGroup::find()
            ->where([
                'country_code' => $countryCode,
                'ndc_type_id' => $ndcTypeId,
                'is_service' => 0,
            ])
            ->with('country')
            ->orderBy(new Expression('country_code, COALESCE(city_id, 0), beauty_level'));

        $didGroupsByCity = [];
        $moscowDidGroups = [];
        /** @var DidGroup $didGroup */
        foreach ($didGroupQuery->each() as $didGroup) {
            if ($didGroup->city_id == City::MOSCOW) {
                $moscowDidGroups[] = $didGroup;
            } else {
                $didGroupsByCity[$didGroup->city_id ?: 0][$didGroup->beauty_level] = $didGroup;
            }
        }

        if ($moscowDidGroups) {
            $didGroupsByCity[City::MOSCOW] = $moscowDidGroups;
        }

        $anyDidgroups = isset($didGroupsByCity[0]) ? $didGroupsByCity[0] : [];

        $data = [];
        $cityQuery = City::find()
            ->where([
                'country_id' => $countryCode,
                'in_use' => 1
            ]);

        foreach ($cityQuery->each() as $city) {
            if (!isset($didGroupsByCity[$city->id]) && !isset($didGroupsByCity[0])) {
                continue;
            }

            if ($city->id == City::MOSCOW) {
                $data[$city->id] = $didGroupsByCity[$city->id];
                continue;
            }

            $cityData = $anyDidgroups;

            if (isset($didGroupsByCity[$city->id])) {
                foreach ($didGroupsByCity[$city->id] as $didGroup) {
                    $cityData[] = $didGroup;
                }
            }

            $data[$city->id] = $cityData;
        }

        return $data;
    }
}
