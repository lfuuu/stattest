<?php

namespace app\health;

use app\helpers\DateTimeZoneHelper;
use app\models\Bill;

/**
 * Все счета, связанные со заявками 1С, должны иметь правильное проведение суммы
 */
class Monitor1cTroubles extends Monitor
{
    private $_data = null;

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 10, 12];
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        return count($this->_getData());
    }

    /**
     * Сообщение
     *
     * @return string
     */
    public function getMessage()
    {
        return implode($this->_getData(), ', ');
    }

    /**
     * Данные по монитору
     *
     * @return array
     */
    private function _getData()
    {
        if ($this->_data !== null) {
            return $this->_data;
        }

        $this->_data = Bill::find()
            ->select('`b`.`bill_no`')
            ->alias('b')
            ->innerJoinWith(['trouble.stage.state s'])
            ->where([
                's.state_1c' => ['Отгружен', 'КОтгрузке', 'Закрыт'],
                'b.sum' => 0,
            ])
            ->andWhere(['>', 'sum_with_unapproved', 0])
            ->andWhere(['>=', 'bill_date', (new \DateTime())->modify('-1 month')->format(DateTimeZoneHelper::DATE_FORMAT)])
            ->column()
        ;

        return $this->_data;
    }
}