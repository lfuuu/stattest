<?php

namespace app\health;

/**
 * Монитор отсутствия оператора (заполнения operator_id) в nnp.route_mnc
 */
class MonitorRouteMncOperator extends Monitor
{
    private $_message = '';

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 5, 10];
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        $values = \Yii::$app->dbPgNnp->createCommand(
            "SELECT DISTINCT operator FROM nnp.route_mnc WHERE operator_id IS NULL ORDER BY operator"
        )->queryColumn();

        $this->_message = implode(', ', $values);

        return count($values);
    }

    public function getMessage()
    {
        return $this->_message;
    }

}