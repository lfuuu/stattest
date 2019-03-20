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
";

        $this->_dataPhones = \Yii::$app->db
            ->createCommand("SELECT DISTINCT client_id as id, 'devadr' as flag_device_address
FROM ({$mainSqlPhoneList}) a
WHERE device_address IS NULL OR trim(device_address) = ''")
            ->queryAll();


        $sqlClients = 'SELECT DISTINCT client_id FROM (' . $mainSqlPhoneList . ') a';

        $sql = "SELECT 
                    c.id, 
                    if (cc.state = 'unchecked', 'unch', '') as flag_contract,
                    if (c.voip_credit_limit_day <= 0, 'vlim', '') as flag_voip_limit 
FROM ({$sqlClients}) a, clients c, client_contract cc
WHERE a.client_id = c.id AND c.contract_id = cc.id
AND (cc.state = 'unchecked' or c.voip_credit_limit_day <= 0)";

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

            isset($clientData['flag_contract']) && $clientData['flag_contract'] && $str[] = $clientData['flag_contract'];
            isset($clientData['flag_voip_limit']) && $clientData['flag_voip_limit'] && $str[] = $clientData['flag_voip_limit'];
            isset($clientData['flag_device_address']) && $clientData['flag_device_address'] && $str[] = $clientData['flag_device_address'];

            return $clientData['id'] . '(' . implode(',', $str) . ')';

        }, $data));
    }
}