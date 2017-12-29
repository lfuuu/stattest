<?php

namespace app\health;


/**
 * Включенные клиенты, не выгружаемые в СОРМ
 */
class MonitorSormClients extends Monitor
{
    const MESSAGE_LENGTH = 50;

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
        $sql = $this->_applyConfigToSql();

        if (!$sql) {
            return 0;
        }

        $this->_data = \Yii::$app->db->createCommand($sql)->queryAll();

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
            $data[] = $value['account_id'] . '(' . $value['count_numbers'] . ')';
        }

        $str = implode(', ', $data);

        return mb_strlen($str) > self::MESSAGE_LENGTH ? mb_substr($str, 0, self::MESSAGE_LENGTH) . '…' : $str;
    }

    /**
     * Формирование основного запроса на основании конфига и шаблона запроса
     *
     * @return string
     */
    private function _applyConfigToSql()
    {
        $data = [];

        foreach (\Yii::$app->params['sormRegions'] as $config) {

            $sql = $this->_getRegionTemplateSql();

            foreach ($config as $key => $value) {
                $sql = str_replace('{' . $key . '}', $value, $sql);
            }

            $data[] = $sql;
        }

        return implode(' UNION ', $data);
    }

    /**
     * Шаблон запроса данных региона
     */
    private function _getRegionTemplateSql()
    {

        return <<<SQL
SELECT
  COUNT(*) AS count_numbers,
  account_id
FROM (
       SELECT
         c.id                                                                    AS account_id,
         CONCAT(u.E164, '-{operator_id}')                                        AS pk,
         u.e164                                                                  AS number,
         {operator_id}                                                           AS operator_id,
         (SELECT contract_no
          FROM client_document
          WHERE contract_id = cc.id AND type = 'contract'
          ORDER BY is_active DESC, contract_date DESC, id DESC
          LIMIT 1)                                                               AS contract_no,
         if(cg.legal_type != 'person', cg.address_jur, cgp.registration_address) AS subscriber_address_unstructured,
         (SELECT name
          FROM client_contract cc, client_contract_business_process_status bps
          WHERE cc.id = contract_id AND business_process_status_id = bps.id)     AS bps_name
       FROM
         (SELECT
            client,
            E164,
            region,
            CONCAT(actual_from, ' 00:00:00')                                    AS actual_from,
            IF(actual_to >= '3000-01-01', NULL, CONCAT(actual_to, ' 23:59:59')) AS actual_to
          FROM usage_voip
          WHERE region = {region_id} AND CAST(NOW() AS DATE) BETWEEN actual_from AND actual_to
          UNION
          SELECT
            c.client,
            u.voip_number AS E164,
            n.region      AS region,
            (SELECT actual_from_utc + INTERVAL 3 HOUR
             FROM uu_account_tariff_log
             WHERE account_tariff_id = u.id
             ORDER BY id
             LIMIT 1)     AS actual_from,
            (SELECT actual_from_utc + INTERVAL 3 HOUR - INTERVAL 1 SECOND
             FROM uu_account_tariff_log
             WHERE account_tariff_id = u.id AND tariff_period_id IS NULL
             ORDER BY id DESC
             LIMIT 1)     AS actual_to
          FROM `uu_account_tariff` u, clients c, voip_numbers n
          WHERE
            n.region = {region_id}
            AND u.service_type_id = 2
            AND c.id = u.client_account_id
            AND n.number = u.voip_number
            AND u.tariff_period_id IS NOT NULL
         ) u,
         clients c,
         client_contract cc,
         client_contragent cg
         LEFT JOIN client_contragent_person cgp ON (cg.id = cgp.contragent_id)
       WHERE
         u.client = c.client
         AND c.contract_id = cc.id
         AND cc.contragent_id = cg.id
         AND u.client NOT IN ('mcnvoip', 'id1279', 'id9130')
         AND u.region = {region_id}
         AND u.E164 LIKE '{number_prefix}%'
       HAVING
         (
           contract_no IS NULL
           OR subscriber_address_unstructured IS NULL
           OR subscriber_address_unstructured = ''
         )
         AND bps_name LIKE 'Включенные'
     ) a
GROUP BY account_id
SQL;

    }
}