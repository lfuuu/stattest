<?php

namespace app\modules\nnp\classes;

use app\classes\model\ActiveRecord;
use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\TranslitHelper;
use app\modules\nnp\models\Number as nnpNumber;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use app\modules\nnp\Module;
use yii\db\Expression;

/**
 * @method static OperatorLinker me($args = null)
 */
class OperatorLinker extends Singleton
{
    /**
     * Актуализировать привязку к операторам
     *
     * @param int $countryCode
     * @return string
     * @throws ModelValidationException
     * @throws \yii\db\Exception
     */
    public function run($countryCode = null)
    {
//        if (NumberRange::isTriggerEnabled()) {
//            throw new \LogicException('Линковка невозможна, потому что триггер включен');
//        }

        $log = '';

        $object = new NumberRange();
        $log .= $this->_setNull($object, $countryCode);
        $log .= $this->_link($object, $countryCode);
        $log .= $this->_updateCnt($object, true, $countryCode);

        if (!$countryCode) {
            $object = new nnpNumber();
            $log .= $this->_setNull($object);
            $log .= $this->_link($object);
            $log .= $this->_updateCnt($object, false);
        }

        $log .= $this->_transliterate($countryCode);

        return $log;
    }

    /**
     * Сбросить привязанные
     *
     * @param ActiveRecord $object
     * @return string
     * @throws \yii\db\Exception
     */
    private function _setNull(ActiveRecord $object, $countryCode = null)
    {
        $object::updateAll(['operator_id' => null], ['operator_source' => ''] + ($countryCode ? ['country_code' => $countryCode] : []));
    }

    /**
     * Привязать к операторам
     *
     * @param ActiveRecord $object
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    private function _link(ActiveRecord $object, $countryCode = null)
    {
        $numberRangeQuery = $object::find()
            ->andWhere('operator_id IS NULL')
            ->andWhere(['IS NOT', 'operator_source', null])
            ->andWhere(['!=', 'operator_source', '']);

        if ($countryCode) {
            $numberRangeQuery->andWhere(['country_code' => $countryCode]);
        }

        if (!$numberRangeQuery->count()) {
            return 'ok ';
        }

        $log = '';

        // Уже существующие объекты
        $operatorSourceToIdQuery = Operator::find()
            ->select([
                'id',
                'name' => new Expression("CONCAT(country_code, '_', LOWER(name))"),
            ]);

        if ($countryCode) {
            $operatorSourceToIdQuery->andWhere(['country_code' => $countryCode]);
        }

        $operatorSourceToId = $operatorSourceToIdQuery
            ->indexBy('name')
            ->column();

        // Уже сделанные соответствия
        $objectQuery = $object::find()
            ->distinct()
            ->select([
                'id' => 'operator_id',
                'name' => new Expression("CONCAT(country_code, '_', LOWER(operator_source))"),
            ])
            ->where('operator_id IS NOT NULL');

        if ($countryCode) {
            $objectQuery->andWhere(['country_code' => $countryCode]);
        }

        $operatorSourceToId += $objectQuery
            ->indexBy('name')
            ->column();

        $i = 0;

        /** @var NumberRange|nnpNumber $numberRange */
        foreach ($numberRangeQuery->each() as $numberRange) {

            if ($i++ % 1000 === 0) {
                $log .= '. ';
            }

            $operatorName = $numberRange->operator_source;
            $operatorName = str_replace('ОАО', 'ПАО', $operatorName);

            $key = $numberRange->country_code . '_' . mb_strtolower($operatorName);
            if (!isset($operatorSourceToId[$key])) {
                $operator = new Operator();
                $operator->name = $operatorName;
                $operator->country_code = $numberRange->country_code;
                if (!$operator->save()) {
                    throw new ModelValidationException($operator);
                }

                $operatorSourceToId[$key] = $operator->id;
            }

            $numberRange->operator_id = $operatorSourceToId[$key];
            if (!$numberRange->save()) {
                throw new ModelValidationException($numberRange);
            }
        }

        return $log;
    }

    /**
     * Обновить столбец cnt
     *
     * @param ActiveRecord $object
     * @param int $isClear
     * @return string
     * @throws \yii\db\Exception
     */
    private function _updateCnt(ActiveRecord $object, $isClear, $countryCode = null)
    {
        $log = '';

        $log .= Module::transaction(
            function () use ($object, $isClear, $countryCode) {
                $db = Operator::getDb();

                $numberRangeTableName = $object::tableName();
                $operatorTableName = Operator::tableName();

                $paramsCountry = $countryCode ? [':country_code' => $countryCode] : [];
                $whereCountry = 'country_code = :country_code';
                if ($isClear) {
                    $sqlClear = "UPDATE {$operatorTableName} SET cnt = 0";

                    if ($countryCode) {
                        $sqlClear .= ' WHERE ' . $whereCountry;
                    }

                    $db->createCommand($sqlClear, $paramsCountry)->execute();
                    unset($sqlClear);

                    $sqlCnt = 'LEAST(COALESCE(SUM(number_to - number_from + 1), 1), 499999999)'; // любое большое число, чтобы не было переполнения
                } else {
                    $sqlCnt = '1';
                }

                $andWhere = '';
                if ($countryCode) {
                    $andWhere = ' AND ' . $whereCountry;
                }

                $sql = <<<SQL
            UPDATE {$operatorTableName}
            SET cnt = LEAST({$operatorTableName}.cnt + operator_stat.cnt, 499999999)
            FROM 
                (
                    SELECT
                        operator_id,
                        {$sqlCnt} AS cnt
                    FROM
                        {$numberRangeTableName} 
                    WHERE
                        operator_id IS NOT NULL 
                        {$andWhere}
                    GROUP BY
                        operator_id
                ) operator_stat
            WHERE {$operatorTableName}.id = operator_stat.operator_id
SQL;
                $db->createCommand($sql, $paramsCountry)->execute();
            }
        );

//        if (!$isClear) {
//            $log .= Module::transaction(
//                function () {
//                    Operator::deleteAll(['cnt' => 0]);
//                }
//            );
//        }

        return $log;
    }

    /**
     * Транслитировать
     */
    private function _transliterate($countryCode = null)
    {
        $operatorQuery = Operator::find()->where(['name_translit' => null] + ($countryCode ? ['country_code' => $countryCode] : []));
        /** @var Operator $operator */
        foreach ($operatorQuery->each() as $operator) {
            $operator->name_translit = TranslitHelper::t($operator->name);
            if (!$operator->save()) {
                throw new ModelValidationException($operator);
            }
        }
    }
}