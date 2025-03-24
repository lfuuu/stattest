<?php

namespace app\health;


/**
 * Мониторинг клиентов, чьи лицевые счета непросчитаны ежесуточным биллером
 */
class MonitorBalanceNotCalculatedDaily extends Monitor
{
    private $_message = '';

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 1, 1];
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        $values = \Yii::$app->db->createCommand(
<<<SQL
SELECT c.id FROM clients c
WHERE account_version=5
and last_account_date < CURDATE()
and created < DATE_ADD(CURDATE(), INTERVAL -1 DAY)
ORDER by c.id desc
LIMIT 30
SQL
        )->queryColumn();

        $this->_message = implode(', ', $values);

        return count($values);
    }

    /**
     * Текстовая интерпритация
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
}