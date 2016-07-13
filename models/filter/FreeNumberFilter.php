<?php

namespace app\models\filter;

use yii\db\Expression;
use app\models\Currency;
use app\models\Number;
use app\models\NumberType;
use app\models\DidGroup;
use app\models\TariffNumber;
use app\models\light_models\NumberLight;

/**
 * Фильтрация для свободных номеров
 */
class FreeNumberFilter extends Number
{

    const FREE_NUMBERS_LIMIT = 12;

    /** @var \yii\db\ActiveQuery */
    private $query;
    private
        $eachMode = false;

    /**
     * @return void
     */
    public function init()
    {
        $this->query = parent::find()->where([parent::tableName() . '.status' => parent::STATUS_INSTOCK]);
    }

    /**
     * Выборка только стандартных номеров
     * @return $this
     */
    public function getNumbers()
    {
        $this->type = NumberType::ID_INTERNAL;
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
    public function setType($numberType = NumberType::ID_INTERNAL)
    {
        $this->query->andWhere([parent::tableName() . '.number_type' => $numberType]);

        switch ($numberType) {
            case NumberType::ID_INTERNAL: {
                $this->query->having(new Expression('
                    IF(
                        `' . parent::tableName() . '`.`number` LIKE "7495%",
                        `' . parent::tableName() . '`.`number` LIKE "74951059%"
                        OR `' . parent::tableName() . '`.`number` LIKE "74951090%"
                        OR `' . parent::tableName() . '`.`beauty_level` IN (1,2),
                        true
                    )
                '));
                break;
            }
            case NumberType::ID_7800: {
                $this->query->andWhere([parent::tableName() . '.ndc' => 800]);
            }
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
            $this->query->andWhere(['>=', TariffNumber::tableName() . '.activation_fee', $minCost])->joinWith('tariff');
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
            $this->query->andWhere(['<=', TariffNumber::tableName() . '.activation_fee', $maxCost])->joinWith('tariff');
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
     * @param null|string $numberPart
     * @param string $pattern
     * @return $this
     */
    public function setNumberMask($mask = null)
    {
        if (!is_null($mask)) {
            $this->query->andWhere(parent::tableName() . '.number LIKE :part', [':part' => $mask]);
        }
        return $this;
    }

    /**
     * @param int $didGroupId
     * @return $this
     */
    public function setDidGroup($didGroupId)
    {
        if ((int) $didGroupId) {
            $this->query->andWhere([parent::tableName() . '.did_group_id' => (int) $didGroupId]);
        }
        return $this;
    }

    /**
     * @param int $cityId
     * @return $this
     */
    public function setCity($cityId)
    {
        if ((int) $cityId) {
            $this->query->andWhere([parent::tableName() . '.city_id' => (int) $cityId]);
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
        $this->query->offset($offset);
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
        $this->query->addOrderBy([TariffNumber::tableName() . '.activation_fee' => $direction])->joinWith('tariff');
        return $this;
    }

    /**
     * @return $this
     */
    public function each()
    {
        $this->eachMode = true;
        return $this;
    }

    /**
     * @param int|null $limit
     * @return null|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
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

        $this->query->limit($limit);

        return $this->eachMode ? $this->query->each() : $this->query->all();
    }

    /**
     * @return null|\yii\db\ActiveRecord
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
     * @param string|false $currency
     * @return array
     */
    public function formattedNumber(\app\models\Number $number, $currency = Currency::RUB)
    {
        $formattedResult = new NumberLight;
        $formattedResult->setAttributes($number->getAttributes());
        $formattedResult->setPrices($number, $currency);

        return $formattedResult;
    }

}