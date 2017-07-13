<?php

namespace app\modules\nnp\classes;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\TranslitHelper;
use app\modules\nnp\models\City;
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
    public function run()
    {
        if (NumberRange::isTriggerEnabled()) {
            throw new \LogicException('Линковка невозможна, потому что триггер включен');
        }

        $log = '';
        $log .= $this->_setNull();
        $log .= $this->_link();
        $log .= $this->_updateCnt();
        $log .= $this->_transliterate();

        return $log;
    }

    /**
     * Сбросить привязанные
     *
     * @return string
     * @throws \yii\db\Exception
     */
    private function _setNull()
    {
        NumberRange::updateAll(['city_id' => null], ['city_source' => '']);
    }

    /**
     * Привязать к городам
     *
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    private function _link()
    {
        $numberRangeQuery = NumberRange::find()
            ->andWhere('city_id IS NULL')
            ->andWhere(['IS NOT', 'city_source', null])
            ->andWhere(['!=', 'city_source', '']);

        if (!$numberRangeQuery->count()) {
            return 'ok';
        }

        $log = '';

        // Уже существующие объекты
        $this->_regionSourceToCityId = City::find()
            ->select([
                'id',
                'name' => new Expression("CONCAT(country_code, '_', region_id, '_', LOWER(name))"),
            ])
            ->indexBy('name')
            ->column();

        // Уже сделанные соответствия
        $this->_regionSourceToCityId += NumberRange::find()
            ->distinct()
            ->select([
                'id' => 'city_id',
                'name' => new Expression("CONCAT(country_code, '_', region_id, '_', LOWER(city_source))"),
            ])
            ->where('city_id IS NOT NULL')
            ->indexBy('name')
            ->column();

        $i = 0;

        /** @var NumberRange $numberRange */
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
     * @return string
     * @throws \yii\db\Exception
     */
    private function _updateCnt()
    {
        $log = '';

        $log .= Module::transaction(
            function () {
                $db = City::getDb();

                $numberRangeTableName = NumberRange::tableName();
                $cityTableName = City::tableName();

                $sql = <<<SQL
            UPDATE {$cityTableName} SET cnt = 0
SQL;
                $db->createCommand($sql)->execute();

                $sql = <<<SQL
            UPDATE {$cityTableName}
            SET cnt = city_stat.cnt
            FROM 
                (
                    SELECT
                        city_id,
                        LEAST(COALESCE(SUM(number_to - number_from + 1), 1), 999999999) AS cnt  -- любое большое число, чтобы не было переполнения
                    FROM
                        {$numberRangeTableName} 
                    WHERE
                        city_id IS NOT NULL 
                    GROUP BY
                        city_id
                ) city_stat
            WHERE {$cityTableName}.id = city_stat.city_id
SQL;
                $db->createCommand($sql)->execute();
            }
        );

        $log .= Module::transaction(
            function () {
                City::deleteAll(['cnt' => 0]);
            }
        );

        return $log;
    }

    /**
     * Транслитировать
     */
    private function _transliterate()
    {
        $cityQuery = City::find()->where(['name_translit' => null]);
        /** @var City $city */
        foreach ($cityQuery->each() as $city) {
            $city->name_translit = TranslitHelper::t($city->name);
            if (!$city->save()) {
                throw new ModelValidationException($city);
            }
        }
    }
}