<?php

namespace app\models\filter;

use yii\db\Expression;
use app\exceptions\web\BadRequestHttpException;
use app\models\Number;
use app\models\NumberType;
use app\models\DidGroup;

/**
 * Фильтрация для свободных номеров
 */
class FreeNumberFilter extends Number
{

    const FREE_NUMBERS_LIMIT = 12;

    /** @var \yii\db\ActiveQuery */
    private $query;
    private
        $eachMode = false,
        $arrayMode = false,
        $minCost = null,
        $maxCost = null;

    /**
     * @return void
     */
    public function init()
    {
        $this->query = parent::find()->where(['status' => parent::STATUS_INSTOCK]);
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
        $this->type = NumberType::ID_EXTERNAL;
        return $this;
    }

    /**
     * @param int $numberType - константа из NumberType
     * @return $this
     */
    public function setType($numberType = NumberType::ID_INTERNAL)
    {
        $this->query->andWhere(['number_type' => $numberType]);

        switch ($numberType) {
            case NumberType::ID_INTERNAL: {
                $this->query->having(new Expression('
                    IF(
                        `number` LIKE "7495%",
                        `number` LIKE "74951059%" OR `number` LIKE "74951090%" OR `beauty_level` IN (1,2),
                        true
                    )
                '));
                break;
            }
            case NumberType::ID_EXTERNAL: {
                $this->query->andWhere(['ndc' => 800]);
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
            $this->query->andWhere(['IN', 'region', $regions]);
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
            $this->minCost = $minCost;
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
            $this->maxCost = $maxCost;
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
                'beauty_level',
                array_filter($beautyLvl, function($row) {
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
            $this->query->andWhere('number LIKE :part', [':part' => $mask]);
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
            $this->query->andWhere(['did_group_id' => $didGroupId]);
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
            $this->query->andWhere(['city_id' => $cityId]);
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
        $this->query->addOrderBy(['price' => $direction]);
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
     * @return $this
     */
    public function asArray()
    {
        $this->arrayMode = true;
        return $this;
    }

    /**
     * @param int|null $limit
     * @return null|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public function result($limit = self::FREE_NUMBERS_LIMIT)
    {
        switch (true) {
            case !is_null($this->minCost) && !is_null($this->maxCost): {
                $this->query->andWhere([
                    'OR',
                    ['BETWEEN', 'price', $this->minCost, $this->maxCost],
                    ['IS', 'price', null],
                ]);
                break;
            }
            case !is_null($this->minCost): {
                $this->query->andWhere([
                    'OR',
                    ['>=', 'price', $this->minCost],
                    ['IS', 'price', null],
                ]);
                break;
            }
            case !is_null($this->maxCost): {
                $this->query->andWhere([
                    'OR',
                    ['<=', 'price', $this->maxCost],
                    ['IS', 'price', null],
                ]);
                break;
            }
        }

        $this->query->addOrderBy([
            new Expression('IF(beauty_level = 0, 10, beauty_level) DESC'),
            'number' => SORT_ASC,
        ]);

        if ($limit === 1) {
            return $this->query->one();
        }

        $this->query->limit($limit);

        if ($this->arrayMode) {
            $this->query->asArray();
        }

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
     * @return null|\yii\db\ActiveRecord
     */
    public function randomOne()
    {
        $union = clone $this->query;

        $this->query
            ->andWhere(['number_cut' => str_pad(mt_rand(0, 99), 2, 0, STR_PAD_LEFT)])
            ->union($union);

        return $this->one();
    }

}