<?php

namespace app\models\filter;

use app\models\Currency;
use app\models\light_models\NumberLight;
use app\models\Number;
use app\models\NumberType;
use yii\db\Expression;

/**
 * Фильтрация для свободных номеров
 *
 * @property FreeNumberFilter numbers
 * @property FreeNumberFilter numbers7800
 */
class FreeNumberFilter extends Number
{

    const FREE_NUMBERS_LIMIT = 12;

    /** @var \yii\db\ActiveQuery */
    private
        $_query,
        $_offset = 0,
        $_mask = null,
        $_similar = null;

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
            ->where([parent::tableName() . '.status' => parent::STATUS_INSTOCK]);
    }

    /**
     * Выборка только стандартных номеров
     *
     * @return $this
     */
    public function getNumbers()
    {
        $this->type = NumberType::ID_GEO_DID;
        return $this;
    }

    /**
     * Выборка номеров типа 7800
     *
     * @return $this
     */
    public function getNumbers7800()
    {
        $this->type = NumberType::ID_7800;
        return $this;
    }

    /**
     * @param int $numberType - константа из NumberType
     * @return $this
     */
    public function setType($numberType = NumberType::ID_GEO_DID)
    {
        $this->_query->andWhere([parent::tableName() . '.number_type' => $numberType]);

        if ($numberType == NumberType::ID_GEO_DID) {
            $this->_query->having(new Expression('
                    IF(
                        `' . parent::tableName() . '`.`number` LIKE "7495%",
                        `' . parent::tableName() . '`.`number` LIKE "74951059%"
                        OR `' . parent::tableName() . '`.`number` LIKE "74951090%"
                        OR `' . parent::tableName() . '`.`beauty_level` IN (1,2),
                        true
                    )
                '));
        }

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
            $this->_query->andWhere(['country_code' => $countryCode]);
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
     * @param null|float $minCost
     * @return $this
     */
    public function setMinCost($minCost = null)
    {
        if (!is_null($minCost)) {
            $this->_query
                ->joinWith('didGroup didGroup1')
                ->andWhere(['>=', 'didGroup1.price1', $minCost]);
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
                ->joinWith('didGroup didGroup2')
                ->andWhere(['<=', 'didGroup2.price1', $maxCost]);
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
            $this->_query->andWhere(parent::tableName() . '.number LIKE :number', [':number' => $mask]);
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
            $this->_query->andWhere([parent::tableName() . '.did_group_id' => (int)$didGroupId]);
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
     * @param mixed $columns
     * @return $this
     */
    public function orderBy($columns)
    {
        $this->_query->addOrderBy($columns);
        return $this;
    }

    /**
     * @param int|null $limit
     * @return \app\models\Number[]
     */
    public function result($limit = self::FREE_NUMBERS_LIMIT)
    {
        $result = $this->_query->all();

        if ($this->_mask) {
            $result = $this->_applyMask($result, $this->_mask);
        }

        if ($this->_similar) {
            $result = $this->_applyLevenshtein($result, $this->_similar);
        }

        if (!is_null($limit)) {
            $result = array_slice($result, (int)$this->_offset, $limit);
        }

        return $result;
    }

    /**
     * Вернуть уникальные NDC по установленным фильтрам
     */
    public function getDistinctNdc()
    {
        $query = clone $this->_query;
        return $query->select(
            [
                'ndc' => new Expression('DISTINCT ndc'),
            ]
        )
            ->asArray()
            ->column();
    }

    /**
     * @return \app\models\Number
     */
    public function one()
    {
        return reset($this->result(1));
    }

    /**
     * @return int|string
     */
    public function count()
    {
        return $this->_query->count();
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
     * @return NumberLight
     */
    public function formattedNumber(\app\models\Number $number, $currency = Currency::RUB)
    {
        $formattedResult = new NumberLight;
        $formattedResult->setAttributes($number->getAttributes());
        $formattedResult->setPrices($number, $currency);

        return $formattedResult;
    }

    /**
     * @param \app\models\Number[] $numbers
     * @param string $mask
     * @return \app\models\Number[]|[]
     */
    private function _applyMask($numbers, $mask)
    {
        $mask = trim($mask);
        $fromEnd = false;

        // Маска не удовлетворяет требованиям
        if (!preg_match('#^[A-Z0-9\*]+$#', $mask)) {
            return [];
        }

        // Поиск начинать с конца
        if (strpos($mask, '*') === 0) {
            $mask = substr($mask, 1, strlen($mask));
            $fromEnd = true;
        }

        // Маска содержит только цифры
        if (preg_match('#^[0-9]+$#', $mask)) {
            return array_filter($numbers, function ($number) use ($mask, $fromEnd) {
                $realNumber = substr($number->number, strlen($number->number) - 7);
                return
                    !$fromEnd ?
                        strpos($realNumber, $mask) !== false :
                        strrpos($realNumber, $mask) + strlen($mask) === strlen($realNumber);
            });
        }

        // Построение регулярного выражения на основе маски
        $pattern = str_split($mask);
        $patternLength = count($pattern);
        $unique = [];
        $regexp = '';

        for ($index = 0; $index < $patternLength; $index++) {
            $symbol = $pattern[$index];

            // Добавление цифр as is
            if (is_numeric($symbol)) {
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

        return array_filter($numbers, function ($number) use ($regexp, $patternLength, $fromEnd) {
            $realNumber = (!$fromEnd) ?
                substr($number->number, strlen($number->number) - 7, $patternLength) :
                substr($number->number, strlen($number->number) - $patternLength);

            return preg_match('#' . $regexp . ($fromEnd ? '$' : '') . '#', $realNumber);
        });
    }

    /**
     * @param \app\models\Number[] $numbers
     * @param string $similar
     * @return \app\models\Number[]
     */
    private function _applyLevenshtein($numbers, $similar)
    {
        array_walk($numbers, function ($row) use ($similar) {
            $row->levenshtein = levenshtein(substr($row->number, strlen($row->number) - 7), $similar);
        });

        usort($numbers, function ($a, $b) {
            if ($a->levenshtein == $b->levenshtein) {
                return 0;
            }

            return ($a->levenshtein < $b->levenshtein) ? -1 : 1;
        });

        return $numbers;
    }

}