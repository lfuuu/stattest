<?php

namespace app\health;


/**
 * ЛС со включенными номерами, не выгружаемые в СОРМ
 */
abstract  class MonitorSormClients extends Monitor
{
    const MESSAGE_LENGTH = 50;

    public $monitorGroup = self::GROUP_FOR_MANAGERS;

    public $regionId = 0;

    private $_data = [];

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 20, 500];
    }

    /**
     * Текущее значение
     *
     * @param int $regionId
     * @return int
     */
    protected function _getValue($regionId)
    {
        $this->_data = \Yii::$app->dbPgSlave->createCommand($this->_getSql($regionId))->queryAll();

        usort($this->_data, function ($a, $b) {
            if ($a['count_numbers'] == $b['count_numbers']) {
                return 0;
            }

            return $a['count_numbers'] < $b['count_numbers'] ? 1 : -1;
        });

        return array_sum(array_column($this->_data, 'count_numbers'));
    }

    /**
     * Получение сообщения для статуса
     *
     * @return string
     */
    public function getMessage()
    {
        $data = [];

        foreach ($this->_data as $value) {
            $data[] = $value['client_account_id'] . '(' . $value['count_numbers'] . ')';
        }

        $str = implode(', ', $data);

        return mb_strlen($str) > self::MESSAGE_LENGTH ? mb_substr($str, 0, self::MESSAGE_LENGTH) . '…' : $str;
    }

    /**
     * Запрос
     * @param integer $regionId
     * @return string
     */
    private function _getSql($regionId)
    {
        return <<<SQL
SELECT
  client_account_id,
  count(*) as count_numbers
FROM "billing"."service_number" sn
LEFT JOIN copm.clients c ON (sn.did = c.number::VARCHAR AND (service_stop IS NULL OR service_stop < NOW()))
WHERE server_id = {$regionId} AND expire_dt > now()
      AND c.number IS NULL AND length(did) > 5
GROUP BY client_account_id
SQL;

    }
}