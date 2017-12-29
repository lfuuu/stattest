<?php

namespace app\health;


/**
 * Неправильный тариф в услуге телефонии
 */

class MonitorBrokenRegionInVoip extends Monitor
{
    public $monitorGroup = self::GROUP_FOR_MANAGERS;

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
     * @return int
     */
    public function getValue()
    {
        $this->_data = \Yii::$app->db->createCommand($this->_getSql())->queryAll();

        return count($this->_data);
    }

    /**
     * Сообщение для пользователей
     *
     * @return string
     */
    public function getMessage()
    {
        return implode(', ', array_column($this->_data, 'e164'));
    }

    private function _getSql()
    {
        return <<<SQL
SELECT
  c.id                    AS client_id,
  uv.id                   AS usage_id,
  uv.e164,
  uv.region               AS region,
  tv1.connection_point_id AS r_tarif,
  tv2.connection_point_id AS r_tarif_local_mob,
  tv3.connection_point_id AS r_tarif_russia,
  tv4.connection_point_id AS r_tarif_russia_mob,
  tv5.connection_point_id AS r_tarif_intern
FROM
  `usage_voip` uv,
  (SELECT
     max(id) max_id,
     id_service
   FROM log_tarif
   WHERE service = 'usage_voip' AND date_activation <= cast(now() AS DATE)
   GROUP BY id_service) AS mt,
  clients c,
  log_tarif lt
  INNER JOIN tarifs_voip tv1 ON (tv1.id = lt.id_tarif)
  INNER JOIN tarifs_voip tv2 ON (tv2.id = lt.id_tarif_local_mob)
  INNER JOIN tarifs_voip tv3 ON (tv3.id = lt.id_tarif_russia)
  INNER JOIN tarifs_voip tv4 ON (tv4.id = lt.id_tarif_russia_mob)
  INNER JOIN tarifs_voip tv5 ON (tv5.id = lt.id_tarif_intern)

WHERE lt.id = mt.max_id AND uv.id = mt.id_service
      AND cast(now() AS DATE) BETWEEN uv.actual_from AND uv.actual_to
      AND c.client = uv.client
HAVING
  (
    region != r_tarif OR
    region != r_tarif_local_mob OR
    region != r_tarif_russia OR
    region != r_tarif_russia_mob OR
    region != r_tarif_intern
  )
ORDER BY ts, client_id, e164

SQL;

    }
}