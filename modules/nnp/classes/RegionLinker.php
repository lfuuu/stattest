<?php

namespace app\modules\nnp\classes;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\TranslitHelper;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Region;
use app\modules\nnp\Module;
use yii\db\Expression;

/**
 * @method static RegionLinker me($args = null)
 */
class RegionLinker extends Singleton
{
    const FUNC_PREG_REPLACE = 'preg_replace'; // замена с помощью регулярного выражения
    const FUNC_STR_REPLACE = 'str_replace'; // строчная замена
    const FUNC_STRPOS = 'strpos'; // замена, если есть вхождение

    protected $preProcessing = [
        [self::FUNC_PREG_REPLACE, '/.*\|/', ''],
        [self::FUNC_STR_REPLACE, 'область', 'обл.'],
        [self::FUNC_STR_REPLACE, 'г.о. ', '',],
        [self::FUNC_STR_REPLACE, 'г. ', '',],
        [self::FUNC_STR_REPLACE, 'р-н ', '',],
        [self::FUNC_STR_REPLACE, 'город ', ''],
        [self::FUNC_STR_REPLACE, 'автономный округ', 'АО'],
        [self::FUNC_STR_REPLACE, 'Республика', ''],
        [self::FUNC_STR_REPLACE, ' - ', '-'],

        [
            self::FUNC_PREG_REPLACE,
            '/Балашиха|Бронницы|Дзержинский|Долгопрудный|Домодедово|Дубна|Жуковский|Звенигород|Ивантеевка|Коломна|Королев|Королёв, Юбилейный|Котельники|Красноармейск|Краснознаменск|Лобня|Лыткарино|Орехово-Зуево|Подольск|Протвино|Пущино|Реутов|Рошаль|Сельцо|Серпухов|Фрязино|Химки|Черноголовка|Электрогорск|Электросталь|Наро-Фоминский, Московская обл.|Щёлковский, Московская обл./',
            'Московская обл.'
        ],

        [self::FUNC_STRPOS, 'Севастополь', 'Крым'],
        [self::FUNC_STRPOS, 'Крым', 'Крым'],
        [self::FUNC_STRPOS, 'Кабардино-Балкарская', 'Кабардино-Балкария'],
        [self::FUNC_STRPOS, 'Карачаево-Черкесская', 'Карачаево-Черкессия'],
        [self::FUNC_STRPOS, 'Удмуртская', 'Удмуртия'],
        [self::FUNC_STRPOS, 'Ханты-Мансийский', 'Ханты-Мансийский АО'],
        [self::FUNC_STRPOS, 'Чувашская', 'Чувашия'],
        [self::FUNC_STRPOS, 'Чеченская', 'Чечня'],
        [self::FUNC_STRPOS, 'Чукотский', 'Чукотка'],
        [self::FUNC_STRPOS, 'Якутия', 'Якутия'],
        [self::FUNC_STRPOS, 'Башкортостан', 'Башкирия'],
        [self::FUNC_STRPOS, 'Камчатский', 'Камчатка'],
        [self::FUNC_STRPOS, 'Татарстан', 'Татарстан'],

        [self::FUNC_STR_REPLACE, 'АО. Ленинский', 'Еврейская автономная обл.'],
        [self::FUNC_STR_REPLACE, 'Губкинский', 'Ямало-Ненецкий АО'],
        [self::FUNC_STR_REPLACE, 'Инская', 'Новосибирская обл.'],
        [self::FUNC_STR_REPLACE, 'Лысьвенский р-н', 'Пермский край'],
        [self::FUNC_STR_REPLACE, 'Москва (Новомосковский)', 'Москва'],
        [self::FUNC_STR_REPLACE, 'Москва (Троицкий)', 'Москва'],
        [self::FUNC_STR_REPLACE, 'н.п. Константиновка', 'Татарстан'],
        [self::FUNC_STR_REPLACE, 'НПС-2 НП Пурпе-Самотлор Пуровский', 'Тюменская обл.'],
        [self::FUNC_STR_REPLACE, 'Сургут и Сургутский район', 'Ханты-Мансийский АО'],
        [self::FUNC_STR_REPLACE, 'Добрянский район', 'Пермский край'],
    ];

    /**
     * Актуализировать привязку к регионам
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
        NumberRange::updateAll(['region_id' => null], ['region_source' => '']);
    }

    /**
     * Привязать к регионам
     *
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    private function _link()
    {
        $numberRangeQuery = NumberRange::find()
            ->andWhere('region_id IS NULL')
            ->andWhere(['IS NOT', 'region_source', null])
            ->andWhere(['!=', 'region_source', '']);

        if (!$numberRangeQuery->count()) {
            return 'ok';
        }

        $log = '';

        // Группированные значение
        $regionSourceToId = Region::find()
            ->select([
                'id',
                'name' => new Expression('CONCAT(country_code, name)'),
            ])
            ->indexBy('name')
            ->column();

        // уже сделанные соответствия
        $regionSourceToId += NumberRange::find()
            ->distinct()
            ->select([
                'id' => 'region_id',
                'name' => new Expression('CONCAT(country_code, region_source)'),
            ])
            ->where('region_id IS NOT NULL')
            ->indexBy('name')
            ->column();

        $i = 0;

        /** @var NumberRange $numberRange */
        foreach ($numberRangeQuery->each() as $numberRange) {

            if ($i++ % 1000 === 0) {
                $log .= '. ';
            }

            if (
                ($key1 = $numberRange->country_code . trim($numberRange->region_source)) &&
                isset($regionSourceToId[$key1])
            ) {

                // оригинальный "исходный регион"
                $numberRange->region_id = $regionSourceToId[$key1];

            } elseif (
                ($regionSource = $this->preProcessing($numberRange->region_source)) &&
                ($key2 = $numberRange->country_code . $regionSource) &&
                isset($regionSourceToId[$key2])
            ) {

                // обработанный "исходный регион"
                $numberRange->region_id = $regionSourceToId[$key2];

            } else {

                // ничего не нашли - создать новый
                $region = new Region();
                $region->name = $regionSource;
                $region->country_code = $numberRange->country_code;
                if (!$region->save()) {
                    throw new ModelValidationException($region);
                }

                $numberRange->region_id = $region->id;

                // добавить в кэш
                if (isset($key1)) {
                    $regionSourceToId[$key1] = $region->id;
                }

                if (isset($key2)) {
                    $regionSourceToId[$key2] = $region->id;
                }
            }

            unset($key1, $key2);

            if (!$numberRange->save()) {
                throw new ModelValidationException($numberRange);
            }
        }

        return $log;
    }

    /**
     * Обработать напильником
     *
     * @param string $value
     * @return string
     */
    protected function preProcessing($value)
    {
        foreach ($this->preProcessing as $preProcessing) {
            switch ($preProcessing[0]) {

                case self::FUNC_PREG_REPLACE:
                    $value = preg_replace($preProcessing[1], $preProcessing[2], $value);
                    break;

                case self::FUNC_STR_REPLACE:
                    $value = str_replace($preProcessing[1], $preProcessing[2], $value);
                    break;

                case self::FUNC_STRPOS:
                    if (strpos($value, $preProcessing[1]) !== false) {
                        $value = $preProcessing[2];
                    }
                    break;
            }
        }

        $value = trim($value);
        return $value;
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
                $db = Region::getDb();
                $numberRangeTableName = NumberRange::tableName();
                $regionTableName = Region::tableName();

                $sql = <<<SQL
            UPDATE {$regionTableName} SET cnt = 0
SQL;
                $db->createCommand($sql)->execute();

                $sql = <<<SQL
            UPDATE {$regionTableName}
            SET cnt = region_stat.cnt
            FROM 
                (
                    SELECT
                        region_id,
                        LEAST(COALESCE(SUM(number_to - number_from + 1), 1), 999999999) AS cnt  -- любое большое число, чтобы не было переполнения
                    FROM
                        {$numberRangeTableName} 
                    WHERE
                        region_id IS NOT NULL 
                    GROUP BY
                        region_id
                ) region_stat
            WHERE {$regionTableName}.id = region_stat.region_id
SQL;
                $db->createCommand($sql)->execute();
            }
        );

        $log .= Module::transaction(
            function () {
                Region::deleteAll(['cnt' => 0]);
            }
        );

        return $log;
    }

    /**
     * Транслитировать
     */
    private function _transliterate()
    {
        $regionQuery = Region::find()->where(['name_translit' => null]);
        /** @var Region $region */
        foreach ($regionQuery->each() as $region) {
            $region->name_translit = TranslitHelper::t($region->name);
            if (!$region->save()) {
                throw new ModelValidationException($region);
            }
        }
    }
}