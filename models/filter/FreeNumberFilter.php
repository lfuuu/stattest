<?php

namespace app\models\filter;

use app\models\Currency;
use app\models\DidGroup;
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
        $query,
        $offset = 0,
        $mask = null,
        $applyMask = false,
        $similar = null,
        $applySimilar = false;

    /**
     * @return void
     */
    public function init()
    {
        $this->query = parent::find()
            ->where([parent::tableName() . '.status' => parent::STATUS_INSTOCK]);
    }

    /**
     * Выборка только стандартных номеров
     * @return $this
     */
    public function getNumbers()
    {
        $this->type = NumberType::ID_GEO_DID;
        return $this;
    }

    /**
     * Выборка номеров типа 7800
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
        $this->query->andWhere([parent::tableName() . '.number_type' => $numberType]);

        if ($numberType == NumberType::ID_GEO_DID) {
            $this->query->having(new Expression('
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
            $this->query->andWhere(['IN', parent::tableName() . '.region', $regions]);
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
            $this->query->andWhere(['country_code' => $countryCode]);
        }
        return $this;
    }

    /**
     * @param string[] $numbers
     * @return $this
     */
    public function setNumbers(array $numbers)
    {
        $this->query->andWhere(['IN', parent::tableName() . '.number', $numbers]);

        return $this;
    }

    /**
     * @param null|float $minCost
     * @return $this
     */
    public function setMinCost($minCost = null)
    {
        if (!is_null($minCost)) {
            $this->query
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
            $this->query
                ->joinWith('didGroup didGroup2')
                ->andWhere(['<=', 'didGroup2.price1', $maxCost]);
        }
        return $this;
    }

    /**
     * @param int[] $beautyLvl
     * @return $this
     */
    public function setBeautyLvl(array $beautyLvl = [])
    {
        if (count($beautyLvl)) {
            $this->query->andWhere([
                'IN',
                parent::tableName() . '.beauty_level',
                array_filter($beautyLvl, function ($row) {
                    return isset(DidGroup::$beautyLevelNames[$row]);
                })
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
            $this->applyMask = true;
            $this->mask = (string)$mask;
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
            $this->query->andWhere(parent::tableName() . '.number LIKE :number', [':number' => $mask]);
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
            $this->query->andWhere([parent::tableName() . '.did_group_id' => (int)$didGroupId]);
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
            $this->query->andWhere([parent::tableName() . '.city_id' => (int)$cityId]);
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
            $this->query->andWhere(['IN', parent::tableName() . '.city_id', $cityIds]);
        }
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset($offset = 0)
    {
        $this->offset = (int)$offset;
        return $this;
    }

    /**
     * @param string|null $similar
     * @return $this
     */
    public function setSimilar($similar = null)
    {
        if (!empty($similar)) {
            $this->applySimilar = true;
            $this->similar = (string)$similar;
        }
        return $this;
    }

    /**
     * @param mixed $columns
     * @return $this
     */
    public function orderBy($columns)
    {
        $this->query->addOrderBy($columns);
        return $this;
    }

    /**
     * @param int $direction
     * @return $this
     */
    public function orderByPrice($direction = SORT_ASC)
    {
        $this->query
            ->joinWith('didGroup')
            ->addOrderBy([DidGroup::tableName() . '.price1' => $direction]);
        return $this;
    }

    /**
     * @param int|null $limit
     * @return null|Number|Number[]
     */
    public function result($limit = self::FREE_NUMBERS_LIMIT)
    {
        $this->query->addOrderBy([
            new Expression('IF(`' . parent::tableName() . '`.`beauty_level` = 0, 10, `' . parent::tableName() . '`.`beauty_level`) DESC'),
            parent::tableName() . '.number' => SORT_ASC,
        ]);

        if ($limit === 1) {
            return $this->query->one();
        }

        $result = $this->query->all();

        if ($this->applyMask) {
            $result = $this->applyMask($result, $this->mask);
        }

        if ($this->applySimilar) {
            $result = $this->applyLevenshtein($result, $this->similar);
        }

        if (!is_null($limit)) {
            $result = array_slice($result, (int)$this->offset, $limit);
        }

        return $result;
    }

    /**
     * @return null|Number
     */
    public function one()
    {
        return $this->result(1);
    }

    /**
     * @return int|string
     */
    public function count()
    {
        return $this->query->count();
    }

    /**
     * @return null|\yii\db\ActiveRecord
     */
    public function randomOne()
    {
        $union = clone $this->query;

        $this->query
            ->andWhere([parent::tableName() . '.number_cut' => str_pad(mt_rand(0, 99), 2, 0, STR_PAD_LEFT)])
            ->union($union);

        return $this->one();
    }

    /**
     * @param \app\models\light_models\NumberLight[] $number
     * @param string|false $currency
     * @return []
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
     * @param Number $number
     * @param string|false $currency
     * @return []
     */
    public function formattedNumber(\app\models\Number $number, $currency = Currency::RUB)
    {
        $formattedResult = new NumberLight;
        $formattedResult->setAttributes($number->getAttributes());
        $formattedResult->setPrices($number, $currency);

        return $formattedResult;
    }

    /**
     * @param Number[] $numbers
     * @param string $mask
     * @return Number[]|[]
     */
    private function applyMask($numbers, $mask)
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
                    !$fromEnd
                        ? strpos($realNumber, $mask) !== false
                        : strrpos($realNumber, $mask) + strlen($mask) === strlen($realNumber);
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
            $realNumber =
                !$fromEnd
                    ? substr($number->number, strlen($number->number) - 7, $patternLength)
                    : substr($number->number, strlen($number->number) - $patternLength);

            return preg_match('#' . $regexp . ($fromEnd ? '$' : '') . '#', $realNumber);
        });
    }

    /**
     * @param Number[] $numbers
     * @param string $similar
     * @return Number[]
     */
    private function applyLevenshtein($numbers, $similar)
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