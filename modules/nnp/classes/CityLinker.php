<?php

namespace app\modules\nnp\classes;

use app\classes\model\ActiveRecord;
use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\TranslitHelper;
use app\modules\nnp\models\City;
use app\modules\nnp\models\Number;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\Module;
use yii\db\Expression;

/**
 * @method static CityLinker me($args = null)
 */
class CityLinker extends Singleton
{
    private $_regionSourceToCityId = [];

    /**
     * Актуализировать привязку к городам
     *
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    public function run($countryCode = null)
    {
//        if (NumberRange::isTriggerEnabled()) {
//            throw new \LogicException('Линковка невозможна, потому что триггер включен');
//        }

        $log = '';
        $object = new NumberRange();
        $log .= $this->_setNull($object, $countryCode);
        $log .= $this->_link($object, $countryCode);
        $log .= $this->_updateCnt($object, true, $countryCode);

        if (!$countryCode) {
            $object = new Number();
            $log .= $this->_setNull($object);
            $log .= $this->_link($object);
            $log .= $this->_updateCnt($object, false);
        }

        $log .= $this->_transliterate();

        return $log;
    }

    /**
     * Сбросить привязанные
     *
     * @param ActiveRecord $object
     * @return string
     * @throws \yii\db\Exception
     */
    private function _setNull(ActiveRecord $object, $countryCode = null)
    {
        $object::updateAll(['city_id' => null], ['city_source' => ''] + ($countryCode ? ['country_code' => $countryCode] : []));
    }

    /**
     * Привязать к городам
     *
     * @param ActiveRecord $object
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    private function _link(ActiveRecord $object, $countryCode = null)
    {
        $numberRangeQuery = $object::find()
            ->andWhere('city_id IS NULL')
            ->andWhere(['IS NOT', 'city_source', null])
            ->andWhere(['!=', 'city_source', '']);

        if ($countryCode) {
            $numberRangeQuery->andWhere(['country_code' => $countryCode]);
        }

        if (!$numberRangeQuery->count()) {
            return 'ok ';
        }

        $log = '';

        // Уже существующие объекты
        $regionSourceToCityIdQuery = City::find()
            ->select([
                'id',
                'name' => new Expression("CONCAT(country_code, '_', region_id, '_', LOWER(name))"),
            ]);

        if ($countryCode) {
            $regionSourceToCityIdQuery->andWhere(['country_code' => $countryCode]);
        }

        $this->_regionSourceToCityId = $regionSourceToCityIdQuery
            ->indexBy('name')
            ->column();

        // Уже сделанные соответствия
        $objectQuery = $object::find()
            ->distinct()
            ->select([
                'id' => 'city_id',
                'name' => new Expression("CONCAT(country_code, '_', region_id, '_', LOWER(city_source))"),
            ])
            ->where('city_id IS NOT NULL');

        if ($countryCode) {
            $objectQuery->andWhere(['country_code' => $countryCode]);
        }

        $this->_regionSourceToCityId += $objectQuery
            ->indexBy('name')
            ->column();

        $i = 0;

        /** @var NumberRange|Number $numberRange */
        foreach ($numberRangeQuery->each() as $numberRange) {

            if ($i++ % 1000 === 0) {
                $log .= '. ';
            }

            $city_id = $this->_findCityByCitySource($numberRange->country_code, $numberRange->region_id, $numberRange->city_source);
            if (!$city_id) {
                continue;
            }

            $numberRange->city_id = $city_id;
            if (!$numberRange->save()) {
                throw new ModelValidationException($numberRange);
            }
        }

        return $log;
    }

    /**
     * Найти город
     *
     * @param string $countryCode
     * @param int $regionId
     * @param string $citySource
     * @return int|null
     * @throws \app\exceptions\ModelValidationException
     */
    private function _findCityByCitySource($countryCode, $regionId, $citySource)
    {
        $citySource = trim($citySource);
        $citySource = str_replace(['г. ', 'город '], '', $citySource);
        if (!$citySource) {
            return null;
        }

        $key = $countryCode . '_' . $regionId . '_' . mb_strtolower($citySource);
        if (array_key_exists($key, $this->_regionSourceToCityId)) {
            // уже обрабатывали
            return $this->_regionSourceToCityId[$key];
        }

        // создать город
        $city = new City;
        $city->name = $citySource;
        $city->country_code = $countryCode;
        $city->region_id = $regionId;
        if (!$city->save()) {
            throw new ModelValidationException($city);
        }

        return $this->_regionSourceToCityId[$key] = $city->id;
    }

    /**
     * Обновить столбец cnt
     *
     * @param ActiveRecord $object
     * @param int $isClear
     * @return string
     * @throws \yii\db\Exception
     */
    private function _updateCnt(ActiveRecord $object, $isClear, $countryCode = null)
    {
        $log = '';

        $log .= Module::transaction(
            function () use ($object, $isClear, $countryCode) {
                $db = City::getDb();

                $numberRangeTableName = $object::tableName();
                $cityTableName = City::tableName();
                $paramsCountry = $countryCode ? [':country_code' => $countryCode] : [];
                $whereCountry = 'country_code = :country_code';

                if ($isClear) {
                    $sqlClear = "UPDATE {$cityTableName} SET cnt = 0, cnt_active=0";
                    if ($countryCode) {
                        $sqlClear .= ' WHERE ' . $whereCountry;
                    }

                    $db->createCommand($sqlClear, $paramsCountry)->execute();
                    unset($sqlClear);

                    $sqlCnt = 'LEAST(COALESCE(SUM(number_to - number_from + 1), 1), 499999999)'; // любое большое число, чтобы не было переполнения
                    $sqlActiveCnt = 'LEAST(COALESCE(SUM(CASE WHEN is_active THEN number_to - number_from + 1 ELSE 0 END), 1), 499999999)';
                } else {
                    $sqlCnt = '1';
                    $sqlActiveCnt = '1';
                }

                $andWhere = '';
                if ($countryCode) {
                    $andWhere = ' AND ' . $whereCountry;
                }

                $sql = <<<SQL
            UPDATE {$cityTableName}
            SET 
                cnt = LEAST({$cityTableName}.cnt + city_stat.cnt, 499999999),
                cnt_active = LEAST({$cityTableName}.cnt_active + city_stat.cnt_active, 499999999)
                
            FROM 
                (
                    SELECT
                        city_id,
                        {$sqlCnt} AS cnt,
                        {$sqlActiveCnt} AS cnt_active
                    FROM
                        {$numberRangeTableName} 
                    WHERE
                        city_id IS NOT NULL 
                        {$andWhere}
                    GROUP BY
                        city_id
                ) city_stat
            WHERE {$cityTableName}.id = city_stat.city_id
SQL;
                $db->createCommand($sql, $paramsCountry)->execute();
            }
        );

        if (!$isClear) {
            $log .= Module::transaction(
                function () {
                    City::deleteAll(['cnt' => 0]);
                }
            );
        }

        return $log;
    }

    /**
     * Транслитировать
     */
    private function _transliterate($countryCode = null)
    {
        $cityQuery = City::find()->where(['name_translit' => null] + ($countryCode ? ['country_code' => $countryCode] : []));
        /** @var City $city */
        foreach ($cityQuery->each() as $city) {
            $city->name_translit = TranslitHelper::t($city->name);
            if (!$city->save()) {
                throw new ModelValidationException($city);
            }
        }
    }
}