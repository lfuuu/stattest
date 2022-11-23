<?php

namespace app\models\filter;

use app\classes\helpers\ArrayHelper;
use app\models\City;
use app\models\ClientAccount;
use app\models\Currency;
use app\models\light_models\NumberLight;
use app\models\Number;
use app\modules\nnp\models\NdcType;
use yii\db\Expression;

/**
 * Фильтрация свободных номеров
 */
class FreeNumberFilter extends Number
{
    // лимиты для обычной выборки
    const LIMIT = 5000; // Старый ЛК не умеет пагинировать, приходится выгружать ему все сразу
    const MAX_LIMIT = 5000;
    const NO_LIMIT = null;

    // лимиты для сгруппированной выборки
    const GROUPED_LIMIT = 10000;

    const REAL_NUMBER_LENGTH = 7;

    /** @var \yii\db\ActiveQuery */
    private $_query;

    /** @var int */
    private $_offset = 0;

    /** @var int */
    private $_limit = self::LIMIT;

    /** @var string */
    private $_mask = null;

    /** @var string */
    private $_similar = null;

    /** @var int */
    private $_totalCount = null;

    /**
     * При клонировании
     */
    public function __clone()
    {
        // Force a copy of this->object, otherwise it will point to same object.
        $this->_query = clone $this->_query;
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->_query = parent::find()
            ->where([
                parent::tableName() . '.status' => parent::STATUS_INSTOCK,
            ]);
    }

    /**
     * @param int $ndcTypeId - константа из NdcType
     * @return $this
     */
    public function setNdcType($ndcTypeId)
    {
        $ndcTypeId = (int)$ndcTypeId;
        if (!$ndcTypeId) {
            return $this;
        }

        $this->_query->andWhere([parent::tableName() . '.ndc_type_id' => $ndcTypeId]);

        return $this;
    }

    /**
     * Фильтруем по статусу склада сим-карты, который импортирован из таблицы sim_card в таблицу voip_numbers
     *
     * @param $warehouseStatusId
     * @return $this
     */
    public function setWarehouseStatus($warehouseStatusId)
    {
        if ($warehouseStatusId == static::STATUS_WAREHOUSE_NO_RELATION) {
            $this->_query->andWhere([parent::tableName() . '.imsi' => null]);
        } else {
            $this->_query->andWhere([parent::tableName() . '.warehouse_status_id' => $warehouseStatusId]);
        }
        // TODO: Список номеров, которые игнорируются при синхронизации
        $this->_query->andWhere(['not in', parent::tableName() . '.number', parent::LIST_SKIPPING]);

        return $this;
    }

    /**
     * @param int[] $regions
     * @return $this
     */
    public function setRegions(array $regions = [])
    {
        if (count($regions)) {
            $this->_query->andWhere(['IN', parent::tableName() . '.region', $regions]);
        }

        return $this;
    }

    /**
     * @param int $countryCode
     * @return $this
     */
    public function setCountry($countryCode = 0)
    {
        if ((int)$countryCode) {
            $this->_query->andWhere([parent::tableName() . '.country_code' => $countryCode]);
        }

        return $this;
    }

    /**
     * @param string[] $numbers
     * @return $this
     */
    public function setNumbers(array $numbers)
    {
        $this->_query->andWhere(['IN', parent::tableName() . '.number', $numbers]);

        return $this;
    }

    /**
     * @param null|int|bool $isService
     * @return $this
     */
    public function setIsService($isService = null)
    {
        if (!is_null($isService)) {
            $this->_query->andWhere([parent::tableName() . '.is_service' => (int)$isService]);
        }

        return $this;
    }

    /**
     * @param null|float $minCost
     * @return $this
     */
    public function setMinCost($minCost = null)
    {
        if (!is_null($minCost)) {
            $this->_query
                ->joinWith('didGroupPriceLevel didGroupPriceLevel1')
                ->andWhere(['and',
                    ['didGroupPriceLevel1.price_level_id' => 1],
                    ['>=', 'didGroupPriceLevel1.price', $minCost]
                ]);
        }

        return $this;
    }

    /**
     * @param null|float $maxCost
     * @return $this
     */
    public function setMaxCost($maxCost = null)
    {
        if (!is_null($maxCost)) {
            $this->_query
                ->joinWith('didGroupPriceLevel didGroupPriceLevel2')
                ->andWhere(['and',
                    ['didGroupPriceLevel2.price_level_id' => 1],
                    ['<=', 'didGroupPriceLevel2.price', $maxCost]
                ]);
        }

        return $this;
    }

    /**
     * @param int $beautyLvl
     * @return $this
     */
    public function setBeautyLvl($beautyLvl = null)
    {
        if (isset($beautyLvl)) {
            $this->_query->andWhere([
                parent::tableName() . '.beauty_level' => $beautyLvl
            ]);
        }

        return $this;
    }

    /**
     * @param null|string $mask
     * @return $this
     */
    public function setNumberMask($mask = null)
    {
        if (!empty($mask)) {
            $this->_mask = (string)$mask;
        }

        return $this;
    }

    /**
     * @param null|string $mask
     * @return $this
     */
    public function setNumberLike($mask = null)
    {
        if (!empty($mask)) {
            $mask = strtr($mask, ['.' => '_', '*' => '%']);
            $mask && $this->_query->andWhere(['LIKE', parent::tableName() . '.number', $mask, $isEscape = false]);
        }

        return $this;
    }

    /**
     * @param int $didGroupId
     * @return $this
     */
    public function setDidGroup($didGroupId)
    {
        if ((int)$didGroupId) {
            $this->_query->andWhere([parent::tableName() . '.did_group_id' => $didGroupId]);
        }

        return $this;
    }

    /**
     * @param int $operatorAccountId
     * @return $this
     */
    public function setOperatorAccount($operatorAccountId)
    {
        if ((int)$operatorAccountId) {
            $this->_query->andWhere([parent::tableName() . '.operator_account_id' => (int)$operatorAccountId]);
        }

        return $this;
    }

    /**
     * @param int $cityId
     * @return $this
     */
    public function setCity($cityId)
    {
        if ((int)$cityId) {
            $this->_query->andWhere([parent::tableName() . '.city_id' => (int)$cityId]);
        }

        return $this;
    }

    /**
     * @param int[] $cityIds
     * @return $this
     */
    public function setCities(array $cityIds = [])
    {
        if (count($cityIds)) {
            $this->_query->andWhere(['IN', parent::tableName() . '.city_id', $cityIds]);
        }

        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset($offset = 0)
    {
        $this->_offset = (int)$offset;
        return $this;
    }

    /**
     * @param int $limit
     * @param int $maxLimit
     * @return $this
     */
    public function setLimit($limit = self::LIMIT, $maxLimit = self::MAX_LIMIT)
    {
        if ($limit === null) {
            $this->_limit = null;
            return $this;
        }

        $limit = (int)$limit;

        if ($limit > 0 && $limit <= $maxLimit) {
            $this->_limit = (int)$limit;
        }

        return $this;
    }

    /**
     * @param string|null $similar
     * @return $this
     */
    public function setSimilar($similar = null)
    {
        if (!empty($similar)) {
            $this->_similar = (string)$similar;
        }

        return $this;
    }

    /**
     * @param int|null $ndc
     * @return $this
     */
    public function setNdc($ndc = null)
    {
        if (!empty($ndc)) {
            $this->_query->andWhere([parent::tableName() . '.ndc' => (int)$ndc]);
        }

        return $this;
    }

    /**
     * @param int|int[] $excludeNdcs
     * @return $this
     */
    public function setExcludeNdcs($excludeNdcs = [])
    {
        if ($excludeNdcs) {
            $this->_query->andWhere(['NOT', [parent::tableName() . '.ndc' => $excludeNdcs]]);
        }

        return $this;
    }

    /**
     * @param mixed $columns
     * @return $this
     */
    public function orderBy($columns)
    {
        $this->_query->addOrderBy($columns);
        return $this;
    }

    /**
     * @param int $minLevel
     * @return $this
     */
    public function setIsShowInLk($minLevel)
    {
        $this->_query
            ->joinWith('city')
            ->andWhere([
                'OR',
                ['!=', parent::tableName() . '.ndc_type_id', NdcType::ID_GEOGRAPHIC],
                ['>=', City::tableName() . '.is_show_in_lk', $minLevel]
            ]);
        return $this;
    }

    /**
     * @param string $source
     * @return $this
     */
    public function setSource($source)
    {
        if ($source) {
            $this->_query->andWhere([parent::tableName() . '.source' => $source]);
        }
        return $this;
    }

    /**
     * @return \app\models\Number[]
     */
    public function result()
    {
        if ($this->_mask) {
            return $this->_resultByMask();
        }

        if ($this->_similar) {
            return $this->_resultByLevenshtein();
        }

        $this->_totalCount = null; // будет посчитано автоматически в $this->count()
        
        return $this->_query
            ->offset($this->_offset)
            ->limit($this->_limit)
            ->all();
    }

    /**
     * @return \app\models\Number[]
     */
    public function resultF()
    {
        if ($this->_mask) {
            return $this->_resultByMask();
        }

        if ($this->_similar) {
            return $this->_resultByLevenshtein();
        }

        $this->_totalCount = null; // будет посчитано автоматически в $this->count()

        $q = $this->_query
            ->offset($this->_offset)
            ->limit($this->_limit);

        return $q->select(['voip_numbers.number', 'voip_numbers.city_id', 'voip_numbers.did_group_id'])->createCommand()->queryAll();
    }
    /**
     * @return \app\models\Number[]
     */
    private function _resultByMask()
    {
        $result = [];
        $limit = $this->_offset + $this->_limit;
        $query = $this->_query;

        foreach ($query->each() as $number) {
            if (!$this->_filterByMask($number)) {
                continue;
            }

            $result[] = $number;
            if (count($result) >= $limit) {
                break;
            }
        }

        $this->_totalCount = count($result); // @todo Это неправильное количество, но правильно посчитать слишком ресурсоемко
        $result = array_slice($result, $this->_offset, $this->_limit);

        return $result;
    }

    /**
     * @return \app\models\Number[]
     */
    private function _resultByLevenshtein()
    {
        $result = $this->_query->all();
        $result = $this->_applyLevenshtein($result, $this->_similar);

        $this->_totalCount = count($result);
        $result = array_slice($result, $this->_offset, $this->_limit);

        return $result;
    }

    /**
     * Вернуть уникальные значения по установленным фильтрам
     *
     * @param string $fieldName
     * @param string $indexBy
     * @return array
     */
    public function getDistinct($fieldName, $indexBy = null)
    {
        $select = ['value' => new Expression('DISTINCT ' . $fieldName)];
        $indexBy && $select[] = $indexBy;
        $query = clone $this->_query;
        return $query->select($select)
            ->indexBy($indexBy)
            ->asArray()
            ->column();
    }

    /**
     * @return \app\models\Number
     */
    public function one()
    {
        $result = $this
            ->setLimit(1)
            ->result();
        return reset($result);
    }

    /**
     * @return int|string
     */
    public function count()
    {
        return is_null($this->_totalCount) ?
            $this->_query->count() :
            $this->_totalCount;
    }

    /**
     * @return \app\models\Number
     */
    public function randomOne()
    {
        $union = clone $this->_query;

        $this->_query
            ->andWhere([parent::tableName() . '.number_cut' => str_pad(mt_rand(0, 99), 2, 0, STR_PAD_LEFT)])
            ->union($union);

        return $this->one();
    }

    /**
     * @param \app\models\light_models\NumberLight[] $numbers
     * @param string $currency
     * @return array
     */
    public function formattedNumbers($numbers = [], $currency = Currency::RUB)
    {
        $result = [];
        foreach ($numbers as $number) {
            $result[] = $this->formattedNumber($number, $currency);
        }

        return $result;
    }

    /**
     * @param \app\models\Number $number
     * @param string $currency
     * @param ClientAccount $clientAccount
     * @param bool $isWithStatistic
     * @return NumberLight
     * @throws \Exception
     */
    public function formattedNumber(\app\models\Number $number, $currency = Currency::RUB, $clientAccount = null, $isWithStatistic = true)
    {
        $formattedResult = new NumberLight;
        $formattedResult->setAttributes($number->getAttributes());
        $formattedResult->setPrices($number, $currency, $clientAccount);
        $formattedResult->country_prefix = $number->country_code ? $number->getCachedCountry()->prefix : null;
        $formattedResult->setCommon($number);
        if ($isWithStatistic) {
            $formattedResult->setCallsStatistic($number);
        }

        return $formattedResult;
    }

    /**
     * @param \app\models\Number $number
     * @return bool
     */
    private function _filterByMask($number)
    {
        $mask = trim($this->_mask);
        if (!$mask) {
            return true;
        }

        // Маска не удовлетворяет требованиям
        if (!preg_match('#^[A-Z0-9\*]+$#', $mask)) {
            return false;
        }

        // Поиск начинать с конца
        $isFromEnd = false;
        if (strpos($mask, '*') === 0) {
            $mask = substr($mask, 1, strlen($mask));
            $isFromEnd = true;
        }

        // Маска содержит только цифры
        if (preg_match('#^\d+$#', $mask)) {
            $realNumber = substr($number->number, strlen($number->number) - self::REAL_NUMBER_LENGTH);
            return $isFromEnd ?
                strrpos($realNumber, $mask) + strlen($mask) === strlen($realNumber) :
                strpos($realNumber, $mask) !== false;
        }

        // Построение регулярного выражения на основе маски
        $pattern = str_split($mask);
        $patternLength = count($pattern);
        $unique = [];
        $regexp = '';

        foreach ($pattern as $index => $symbol) {
            if (is_numeric($symbol)) {
                // Добавление цифр as is
                $regexp .= $symbol;
                continue;
            }

            if (!isset($unique[$symbol])) {
                $regexp .= $index ? '((?![\\' . implode('\\', array_values($unique)) . '])\d)' : '(\d)';
                $unique[$symbol] = count(array_keys($unique)) + 1;
            } else {
                $regexp .= '\\' . $unique[$symbol];
            }
        }

        $realNumber = $isFromEnd ?
            substr($number->number, strlen($number->number) - $patternLength) :
            substr($number->number, strlen($number->number) - self::REAL_NUMBER_LENGTH, $patternLength);

        return preg_match('#' . $regexp . ($isFromEnd ? '$' : '') . '#', $realNumber);
    }

    /**
     * @param \app\models\Number[] $numbers
     * @param string $similar
     * @return \app\models\Number[]
     */
    private function _applyLevenshtein($numbers, $similar)
    {
        $result = [];
        for ($i = 0; $i <= 15; $i++) {
            // чтобы индексы были в нужном порядке
            // 15 - максимальная длина номера по E164
            $result[$i] = [];
        }

        foreach ($numbers as $number) {
            $realNumber = substr($number->number, strlen($number->number) - self::REAL_NUMBER_LENGTH);
            $levenshtein = levenshtein($realNumber, $similar);
            $result[$levenshtein][] = $number;
        };

        return ArrayHelper::flatten($result);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        return $this->_query;
    }
}