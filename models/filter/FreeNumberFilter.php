<?php

namespace app\models\filter;

use app\models\DidGroup;
use yii\db\Expression;
use app\exceptions\web\BadRequestHttpException;
use app\models\Number;
use app\models\NumberType;

/**
 * Фильтрация для свободных номеров
 */
class FreeNumberFilter extends Number
{

    const FREE_NUMBERS_LIMIT = 12;

    private $query;

    /**
     * @return void
     */
    public function init()
    {
        $this->query = parent::find()->where(['status' => parent::STATUS_INSTOCK]);
    }

    /**
     * @return $this
     */
    public function getNumbers()
    {
        $this->type = NumberType::ID_INTERNAL;
        return $this;
    }

    /**
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
     * @param array $regions
     * @param bool|false $filterMode
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
            $this->query->andWhere(['>=', 'price', $minCost]);
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
            $this->query->andWhere(['<=', 'price', $maxCost]);
        }
        return $this;
    }

    /**
     * @param int $beautyLvl
     * @return $this
     */
    public function setBeautyLvl($beautyLvl)
    {
        if (!in_array($beautyLvl, array_keys(DidGroup::$beautyLevelNames))) {
            throw new BadRequestHttpException('Bad variant of beauty level');
        }

        $this->query->andWhere(['beauty_level' => (int) $beautyLvl]);
        return $this;
    }

    /**
     * @param null|string $numberPart
     * @param string $pattern
     * @return $this
     */
    public function setNumberMask($numberPart = null, $pattern = '^%?\d{3,}%$')
    {
        if (!is_null($numberPart)) {
            if (!preg_match('#' . $pattern . '#', $numberPart)) {
                throw new BadRequestHttpException('Bad format for mask search' . $pattern);
            }
            $this->query->andWhere('number LIKE :part', [':part' => $numberPart]);
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
     * @param int $offset
     * @return $this
     */
    public function setOffset($offset = 0)
    {
        $this->query->offset($offset);
        return $this;
    }

    /**
     * @param int $descination
     * @return $this
     */
    public function orderByPrice($descination = SORT_ASC)
    {
        $this->query->addOrderBy(['price' => $descination]);
        return $this;
    }

    /**
     * @return $this
     */
    public function orderByRand()
    {
        $this->query->orderBy('RAND()');
        return $this;
    }

    /**
     * @param int|null $limit
     * @return null|\yii\db\ActiveRecord[]
     */
    public function result($limit = self::FREE_NUMBERS_LIMIT)
    {
        $this->query->addOrderBy([
            new Expression('IF(beauty_level = 0, 10, beauty_level) DESC'),
            'number' => SORT_ASC,
        ]);

        if (is_null($limit)) {
            return $this->query->all();
        }

        if ($limit === 1) {
            return $this->query->one();
        }

        return $this->query->limit($limit)->all();
    }

}