<?php

namespace app\health;

use app\classes\Assert;
use app\models\filter\SormClientFilter;

/**
 * Используемые номера в регионе сормирования, и не выгружаемые
 */
class MonitorSormItGrad extends Monitor
{
    public $monitorGroup = self::GROUP_FOR_MANAGERS;

    protected $region_id;

    private $_dataClient = [];
    private $_dataPhones = [];

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 2, 10];
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        Assert::isNotEmpty($this->region_id);

        $sqlPhoneList = SormClientFilter::getSqlPhoneList($this->region_id);

        $mainSqlPhoneList = "SELECT a.*
FROM ({$sqlPhoneList}) a, clients c, client_contract cc
WHERE a.client_id = c.id AND c.contract_id = cc.id
AND cc.business_process_status_id NOT IN (22, 28)
AND c.voip_credit_limit_day > 0
";

        $this->_dataPhones = \Yii::$app->db
            ->createCommand("SELECT DISTINCT client_id as id, 'devadr' as flag_device_address
FROM ({$mainSqlPhoneList}) a
WHERE device_address IS NULL OR trim(device_address) = ''")
            ->queryAll();


        $sqlClients = 'SELECT DISTINCT client_id FROM (' . $mainSqlPhoneList . ') a';

        $sql = "SELECT *
FROM (
       SELECT
         c.id,
         IF(cc.state = 'unchecked', 'unch', '')        as flag_contract,
         if(c.pay_acc IS NULL OR TRIM(c.pay_acc) = '' OR c.bank_name IS NULL OR TRIM(c.bank_name) = '', 'bank',
            '')                                        as flag_bank,
         if(ifnull((SELECT trim(data)
                    FROM client_contacts cct
                    WHERE cct.client_id = c.id /*AND is_active = 1 AND is_official = 1*/ AND DATA != 'test' AND
                          type = 'phone' AND
                          COMMENT != 'autoconvert' AND COMMENT != '' AND data != '' AND user_id != 177 AND
                          comment IS NOT NULL AND data IS NOT NULL
                    ORDER BY LENGTH(comment) DESC, id DESC
                    LIMIT 1), '') = '', 'contact', '') AS flag_contact
       FROM ({$sqlClients}) a, clients c, client_contract cc
       WHERE
         a.client_id = c.id
         AND c.contract_id = cc.id
     ) a
WHERE a.flag_contract != '' OR a.flag_bank != ''";

        $this->_dataClient = \Yii::$app->db->createCommand($sql)->queryAll();

        return count($this->_dataClient);
    }

    public function getMessage()
    {
        $data = array_combine(array_column($this->_dataClient, 'id'), $this->_dataClient);

        foreach ($this->_dataPhones as $phone) {
            $data[$phone['id']]['id'] = $phone['id'];
            $data[$phone['id']]['flag_device_address'] = $phone['flag_device_address'];
        }

        return implode(', ', array_map(function ($clientData) {
            $str = [];

            foreach(['flag_contract', 'flag_contact', 'flag_device_address', 'flag_bank'] as $field) {
                isset($clientData[$field]) && $clientData[$field] && $str[] = $clientData[$field];
            }

            return $clientData['id'] . '(' . implode(',', $str) . ')';

        }, $data));
    }
}