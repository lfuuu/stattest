<?php

namespace app\classes;

use app\classes\adapters\EbcKafka;
use app\dao\ClientSuperDao;

class ChangeClientStructureRegistrator extends Singleton
{
    const ACCOUNT = 'account';
    const CONTRACT = 'contract';
    const CONTRAGENT = 'contragent';
    const SUPER = 'super';

    const EVENT = 'change_client_struct';
    const TOPIC = 'stat-event--full-client-struct';

    const objTypes = [
        self::ACCOUNT,
        self::CONTRACT,
        self::CONTRAGENT,
        self::SUPER,
    ];

    /**
     * @return \yii\redis\Connection|false
     */
    private function _getRedis()
    {
        return \Yii::$app->redis ?? false;
    }

    public function registrChange($obj, $value)
    {
        $redis = $this->_getRedis();
        if (!$redis) {
            return false;
        }

        if (!in_array($obj, self::objTypes)) {
            throw new \InvalidArgumentException('Unknown object type: ' . $obj);
        }

        $redis->sadd($obj, $value);
    }

    public function getData()
    {
        $redis = $this->_getRedis();
        if (!$redis) {
            return false;
        }

        $r = [];

        foreach ([
                     'accountIds' => self::ACCOUNT,
                     'contractIds' => self::CONTRACT,
                     'contragentIds' => self::CONTRAGENT,
                     'clientIds' => self::SUPER,
                 ] as $arrayKey => $redisKey) {
            $v = $redis->sinter($redisKey);
            if ($v) {
                $r[$arrayKey] = array_map(function ($id) {
                    return (int)$id;
                }, $v);
            }
        }

        return $r;
    }

    public function truncateData()
    {
        $redis = $this->_getRedis();
        if (!$redis) {
            return false;
        }

        foreach (self::objTypes as $type) {
            $redis->del($type);
        }

        return true;
    }

    public function checkDataForSend()
    {
        $data = $this->getData();

        if (!$data) {
            return ;
        }

        $this->truncateData();

        return $data;
    }

    public function anonce($superId)
    {
        return EbcKafka::me()->sendMessage(
            self::TOPIC,
            ClientSuperDao::me()->getSuperClientStructByIds([$superId]),
            (string)$superId
        );
    }
}
