<?php

namespace app\modules\nnp\classes;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\modules\nnp\filter\NumberRangeFilter;
use app\modules\nnp\models\FilterQuery;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\NumberRangePrefix;
use app\modules\nnp\models\Prefix;

/**
 * @method static RefreshPrefix me($args = null)
 */
class RefreshPrefix extends Singleton
{
    private $_fields = [
        'country_code',
        'operator_id',
        'region_id',
        'city_id',
        'ndc_type_id'
    ];

    /**
     * Попытаться найти фильтр
     *
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    public function refreshByFilter()
    {
        if (NumberRange::isTriggerEnabled()) {
            throw new \LogicException('Обновление префиксов невозможно, потому что триггер включен');
        }

        $log = '';
        $numberRangePrefixTableName = NumberRangePrefix::tableName();
        $prefixQuery = Prefix::find(); // ->where(['id' => 18]);
        $filterQueryModelName = (new NumberRangeFilter)->getClassName();
        FilterQuery::deleteAll(['model_name' => (new NumberRangePrefix)->getClassName()]);

        /** @var Prefix $prefix */
        foreach ($prefixQuery->each() as $prefix) {

            $log .= $prefix->name . ': ' . PHP_EOL;

            $differentValues = [];
            foreach ($this->_fields as $field) {
                $differentValues[$field] = [];
            }

            $numberRangeQuery = NumberRange::find()
                ->select(implode(', ', $this->_fields))
                ->distinct()
                ->joinWith('numberRangePrefixes', false, 'INNER JOIN')
                ->andWhere([$numberRangePrefixTableName . '.prefix_id' => $prefix->id])
                ->asArray();

            foreach ($numberRangeQuery->all() as $row) {
                foreach ($this->_fields as $field) {
                    if ($row[$field]) {
                        $differentValues[$field][$row[$field]] = $row[$field];
                    }
                }
            }

            $filterQuery = new FilterQuery();
            $filterQuery->id = $prefix->id;
            $filterQuery->name = $prefix->name;
            $filterQuery->data = [];

            foreach ($this->_fields as $field) {
                $log .= $field . ' = ' . implode(', ', $differentValues[$field]) . PHP_EOL;

                $values = array_values($differentValues[$field]);
                if (!count($values)) {
                    continue;
                }

                $filterQuery->data[$field] = (count($values) == 1) ? reset($values) : $values;
            }

            $filterQuery->model_name = $filterQueryModelName;
            if ($filterQuery->data && !$filterQuery->save()) {
                throw new ModelValidationException($filterQuery);
            }

            $log .= PHP_EOL;
        }

        return $log;
    }

    /**
     * Найти новые диапазоны вместо выключенных
     *
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    public function refreshByRange()
    {
        if (NumberRange::isTriggerEnabled()) {
            throw new \LogicException('Обновление префиксов невозможно, потому что триггер включен');
        }

        $log = '';
        $numberRangeTableName = NumberRange::tableName();
        $numberRangePrefixTableName = NumberRangePrefix::tableName();
        $prefixQuery = Prefix::find(); // ->where(['id' => 45]);
        $numberRangeFilter = new NumberRangeFilter;

        // по всем префиксам
        /** @var Prefix $prefix */
        foreach ($prefixQuery->each() as $prefix) {

            $log .= $prefix->name . ' ';

            $sql = <<<SQL
                SELECT
                    DISTINCT number_range_active.id
                FROM
                    {$numberRangePrefixTableName} number_range_prefix_inactive,
                    {$numberRangeTableName} number_range_inactive,
                    {$numberRangeTableName} number_range_active
                WHERE
                    number_range_prefix_inactive.prefix_id = {$prefix->id}
                    AND number_range_prefix_inactive.number_range_id = number_range_inactive.id
                    AND NOT number_range_inactive.is_active
                    AND number_range_active.is_active
                    AND (
                        number_range_active.full_number_from 
                            BETWEEN number_range_inactive.full_number_from 
                            AND number_range_inactive.full_number_to
                        OR
                        number_range_active.full_number_to 
                            BETWEEN number_range_inactive.full_number_from 
                            AND number_range_inactive.full_number_to
                        )
SQL;
            $log .= $numberRangeFilter->addFilterModelToPrefix($sql, $prefix->id);
            $log .= PHP_EOL;
        }

        return $log;
    }
}