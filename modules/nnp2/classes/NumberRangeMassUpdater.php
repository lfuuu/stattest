<?php

namespace app\modules\nnp2\classes;

use app\classes\Connection;
use app\classes\Singleton;
use app\modules\nnp2\models\GeoPlace;
use app\modules\nnp2\models\NdcType;
use app\modules\nnp2\models\NumberRange;
use app\modules\nnp2\models\Operator;

/**
 * @method static NumberRangeMassUpdater me($args = null)
 */
class NumberRangeMassUpdater extends Singleton
{
    /**
     * Обновить подтвержденность диапазонов
     *
     * @param int|null $geoPlaceId
     * @param int|null $ndcTypeId
     * @param int|null $operatorId
     * @return bool
     * @throws \yii\db\Exception
     */
    public function update($geoPlaceId = null, $ndcTypeId = null, $operatorId = null)
    {
        /** @var Connection $dbPgNnp2 */
        $dbPgNnp2 = NumberRange::getDb();

        $tableName = NumberRange::tableName();
        $tableGeo = GeoPlace::tableName();
        $tableNdcType = NdcType::tableName();
        $tableOperator = Operator::tableName();

        $condition = '';
        if ($geoPlaceId && is_int($geoPlaceId)) {
            $condition .= '    AND "nr"."geo_place_id" = ' . $geoPlaceId;
        }
        if ($ndcTypeId && is_int($ndcTypeId)) {
            $condition .= '    AND "nr"."ndc_type_id" = ' . $ndcTypeId;
        }
        if ($operatorId && is_int($operatorId)) {
            $condition .= '    AND "nr"."operator_id" = ' . $operatorId;
        }

        if (!$condition) {
            return false;
        }

        $sql = <<<SQL
UPDATE
    {$tableName} "nr"
SET
    "is_valid" = ("g"."is_valid" AND "ndc"."is_valid" AND "o"."is_valid")
FROM
     {$tableGeo} "g",
     {$tableNdcType} "ndc",
     {$tableOperator} "o"
WHERE
    "nr"."geo_place_id" = "g"."id"
    AND "nr"."ndc_type_id" = "ndc"."id"
    AND "nr"."operator_id" = "o"."id"
{$condition}
SQL;

        $dbPgNnp2->createCommand($sql)->execute();
        return true;
    }
}