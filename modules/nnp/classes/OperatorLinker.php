<?php

namespace app\modules\nnp\classes;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\helpers\TranslitHelper;
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
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    public function run()
    {
        if (NumberRange::isTriggerEnabled()) {
            throw new \LogicException('Линковка невозможна, потому что триггер включен');
        }

        $log = '';
        $log .= $this->_setNull();
        $log .= $this->_link();
        $log .= $this->_updateCnt();
        $log .= $this->_transliterate();

        return $log;
    }

    /**
     * Сбросить привязанные
     *
     * @return string
     * @throws \yii\db\Exception
     */
    private function _setNull()
    {
        NumberRange::updateAll(['operator_id' => null], ['operator_source' => '']);
    }

    /**
     * Привязать к операторам
     *
     * @return string
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    private function _link()
    {
        $numberRangeQuery = NumberRange::find()
            ->andWhere('operator_id IS NULL')
            ->andWhere(['IS NOT', 'operator_source', null])
            ->andWhere(['!=', 'operator_source', '']);

        if (!$numberRangeQuery->count()) {
            return 'ok';
        }

        $log = '';

        // Уже существующие объекты
        $operatorSourceToId = Operator::find()
            ->select([
                'id',
                'name' => new Expression("CONCAT(country_code, '_', LOWER(name))"),
            ])
            ->indexBy('name')
            ->column();

        // Уже сделанные соответствия
        $operatorSourceToId += NumberRange::find()
            ->distinct()
            ->select([
                'id' => 'operator_id',
                'name' => new Expression("CONCAT(country_code, '_', LOWER(operator_source))"),
            ])
            ->where('operator_id IS NOT NULL')
            ->indexBy('name')
            ->column();

        $i = 0;

        /** @var NumberRange $numberRange */
        foreach ($numberRangeQuery->each() as $numberRange) {

            if ($i++ % 1000 === 0) {
                $log .= '. ';
            }

            $key = $numberRange->country_code . '_' . mb_strtolower($numberRange->operator_source);
            if (!isset($operatorSourceToId[$key])) {
                $operator = new Operator();
                $operator->name = $numberRange->operator_source;
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
     * @return string
     * @throws \yii\db\Exception
     */
    private function _updateCnt()
    {
        $log = '';

        $log .= Module::transaction(
            function () {
                $db = Operator::getDb();

                $numberRangeTableName = NumberRange::tableName();
                $operatorTableName = Operator::tableName();

                $sql = <<<SQL
            UPDATE {$operatorTableName} SET cnt = 0
SQL;
                $db->createCommand($sql)->execute();

                $sql = <<<SQL
            UPDATE {$operatorTableName}
            SET cnt = operator_stat.cnt
            FROM 
                (
                    SELECT
                        operator_id,
                        LEAST(COALESCE(SUM(number_to - number_from + 1), 1), 999999999) AS cnt  -- любое большое число, чтобы не было переполнения
                    FROM
                        {$numberRangeTableName} 
                    WHERE
                        operator_id IS NOT NULL 
                    GROUP BY
                        operator_id
                ) operator_stat
            WHERE {$operatorTableName}.id = operator_stat.operator_id
SQL;
                $db->createCommand($sql)->execute();
            }
        );

        $log .= Module::transaction(
            function () {
                Operator::deleteAll(['cnt' => 0]);
            }
        );

        return $log;
    }

    /**
     * Транслитировать
     */
    private function _transliterate()
    {
        $operatorQuery = Operator::find()->where(['name_translit' => null]);
        /** @var Operator $operator */
        foreach ($operatorQuery->each() as $operator) {
            $operator->name_translit = TranslitHelper::t($operator->name);
            if (!$operator->save()) {
                throw new ModelValidationException($operator);
            }
        }
    }
}