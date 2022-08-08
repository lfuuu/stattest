<?php

namespace app\classes;

use app\models\EventQueue;

class ChangeClientStructureRegistrator extends Singleton
{
    const ACCOUNT = 'account';
    const CONTRACT = 'contract';
    const CONTRAGENT = 'contragent';
    const SUPER = 'super';

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

        $r = [
            'account_id' => $redis->sinter(self::ACCOUNT),
            'contract_id' => $redis->sinter(self::CONTRACT),
            'contragent_id' => $redis->sinter(self::CONTRAGENT),
            'client_id' => $redis->sinter(self::SUPER),
        ];

        // format
        $r = array_map(function ($ids) {
            return array_map(function ($id) {
                return (int)$id;
            }, $ids);
        }, $r);

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

        $isRegistrChanges = false;
        foreach ($data as $ids) {
            if ($ids) {
                $isRegistrChanges = true;
                break;
            }
        }

        if (!$isRegistrChanges) {
            return false;
        }

        EventQueue::go(EventQueue::SYNC_CLIENT_CHANGED, $data);

        $this->truncateData();

        return $data;
    }
}
