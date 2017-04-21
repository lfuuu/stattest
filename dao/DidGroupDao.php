<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\models\Number;
use Yii;
use yii\db\Expression;
use yii\db\Query;

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
            } else {
                $where = [
                    'country_code' => $number->country_code,
                    'city_id' => $number->city_id,
                    'beauty_level' => $number->beauty_level,
                ];
            }
        }

        $query = DidGroup::find()
            ->where($where ?: [
                'AND',
                ['country_code' => $number->country_code],
                ['beauty_level' => $number->beauty_level],
                [
                    'OR',
                    ['city_id' => $number->city_id],
                    ['city_id' => null]
                ]
            ]);

        return $query->max('id');
    }

    /**
     * Получение DID-групп по городу
     *
     * @param City $city
     * @return array
     */
    public function getQueryWhereByCity(City $city)
    {
        if ($city->id == City::MOSCOW) { // исключение
            $where = [
                'country_code' => $city->country_id,
                'city_id' => $city->id
            ];
        } else {
            $query = DidGroup::find()
                ->select('MAX(id)')
                ->where([
                    'AND',
                    ['country_code' => $city->country_id],
                    [
                        'OR',
                        ['city_id' => $city->id],
                        ['city_id' => null]
                    ]
                ])
                ->groupBy('beauty_level');

            $where = ['id' => $query];
        }

        return $where;
    }

    /**
     * Получение списка DID-групп в стране с индексом по городам
     *
     * @param int $countryCode
     * @return array
     */
    public function getDidgroupsByCity($countryCode)
    {
        $didGroupQuery = DidGroup::find()
            ->where([
                'country_code' => $countryCode
            ])
            ->with('country')
            ->orderBy(new Expression('country_code, COALESCE(city_id, 0), beauty_level'));

        $didGroupsByCity = [];
        /** @var DidGroup $didGroup */
        foreach ($didGroupQuery->each() as $didGroup) {
            $didGroupsByCity[$didGroup->city_id ?: 0][$didGroup->beauty_level] = $didGroup;
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

            $cityData = $anyDidgroups;

            if (isset($didGroupsByCity[$city->id])) {
                foreach ($didGroupsByCity[$city->id] as $beautyLevel => $didGroup) {
                    $cityData[$beautyLevel] = $didGroup;
                }
            }

            $data[$city->id] = $cityData;
        }

        return $data;
    }

    /**
     * Назначение DID-групп к номерам
     *
     * @throws \Exception
     */
    public function applyDidGroupToNumbers()
    {
        $didGroupQuery = DidGroup::find()
            ->orderBy(new Expression('country_code, COALESCE(city_id, 0), beauty_level'));

        $transaction = Yii::$app->db->beginTransaction();

        try {
            /** @var DidGroup $group */
            foreach ($didGroupQuery->each() as $group) {

                $where = [
                    'country_code' => $group->country_code,
                    'beauty_level' => $group->beauty_level
                ];

                if ($group->city_id) {
                    $where = [
                        'AND',
                        $where,
                        ['city_id' => $group->city_id]
                    ];
                }

                if ($didgroupAdditionWhere = self::getDidgroupAdditionWhere($group)) {
                    $where = [
                        'AND',
                        $where,
                        $didgroupAdditionWhere
                    ];
                }

                Number::updateAll(['did_group_id' => $group->id], $where);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            throw $e;
        }
    }

    /**
     * Получение дополнительного условия для выборки номеров по DID-группе
     *
     * @param DidGroup $group
     * @param int $didGroupId
     * @return array|bool
     */
    public function getDidgroupAdditionWhere(DidGroup $group = null, $didGroupId = 0)
    {
        if ($group) {
            $didGroupId = $group->id;
        }

        if ($didGroupId == DidGroup::ID_MOSCOW_STANDART_495) {
            return ['like', 'number', '7495%', $isEscape = false];
        } elseif ($group->id == DidGroup::ID_MOSCOW_STANDART_499) {
            return ['like', 'number', '7499%', $isEscape = false];
        }

        return false;
    }
}
