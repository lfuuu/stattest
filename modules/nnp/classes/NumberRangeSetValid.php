<?php

namespace app\modules\nnp\classes;

use app\classes\Connection;
use app\classes\Singleton;
use app\modules\nnp\models\NumberRange;
use yii\db\Expression;

/**
 * @method static NumberRangeSetValid me($args = null)
 */
class NumberRangeSetValid extends Singleton
{
    /**
     * Обновить подтвержденность диапазонов
     *
     * @param int $countryCode
     * @param int $operatorId
     * @param int $cityId
     * @param int $regionId
     * @return bool
     * @throws \yii\db\Exception
     */
    public function set($countryCode, $operatorId = null, $cityId = null, $regionId = null): string
    {

        /** @var Connection $dbPgNnp */
        $dbPgNnp = NumberRange::getDb();

        $tableName = NumberRange::tableName();

        $query = NumberRange::find()
            ->alias('nr')
            ->where(['nr.country_code' => $countryCode])
            ->joinWith('operator o')
            ->joinWith('city c')
            ->joinWith('region r')

            ->select(['nr.id', 'is_valid' => new Expression('COALESCE(o.is_valid, false) AND COALESCE(c.is_valid, true) AND COALESCE(r.is_valid, true)')])
            ;

        $operatorId && $query->andWhere(['nr.operator_id' => $operatorId]);
        $cityId && $query->andWhere(['nr.city_id' => $cityId]);
        $regionId && $query->andWhere(['nr.region_id' => $regionId]);

        $subSql = $query->createCommand($dbPgNnp)->rawSql;

        $sql = <<<SQL
UPDATE
    {$tableName} a
SET
    is_valid = q.is_valid
FROM
     ({$subSql}) q
WHERE
        a.id = q.id
SQL;

        return (string)$dbPgNnp->createCommand($sql)->execute();
    }
}