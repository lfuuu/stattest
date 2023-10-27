<?php

namespace app\classes\dto;

use app\classes\adapters\EbcKafka;
use app\classes\Singleton;
use app\classes\Utils;
use app\dao\ClientSuperDao;
use app\models\ClientStructureChangeRegistry;
use app\models\EventQueue;

class ChangeClientStructureRegistratorDto extends Singleton
{
    const ACCOUNT = 'account';
    const CONTRACT = 'contract';
    const CONTRAGENT = 'contragent';
    const SUPER = 'super';

    const EVENT = 'change_client_struct';
    const TOPIC = 'stat-event--full-client-struct';

    const objNames = [
        self::ACCOUNT => 'accountIds',
        self::CONTRACT => 'contractIds',
        self::CONTRAGENT => 'contragentIds',
        self::SUPER => 'clientIds',
    ];

    public function registrChange($obj, $value)
    {
        if (!$value) {
            return false;
        }

        if (!isset(self::objNames[$obj])) {
            throw new \InvalidArgumentException('Unknown object type: ' . $obj);
        }

        return ClientStructureChangeRegistry::add($obj, $value);
    }

    public function getData(): array
    {
        $data = ClientStructureChangeRegistry::find()->all();
        $result = [];

        foreach ($data as $r) {
            $name = self::objNames[$r->section];
            if (!isset($result[$name])) {
                $result[$name] = [];
            }
            $result[$name][] = $r->model_id;
        }

        return $result;
    }

    public function truncateData()
    {
        ClientStructureChangeRegistry::deleteAll();

        return true;
    }

    public function checkDataForSend()
    {
        $table = ClientStructureChangeRegistry::tableName();
        ClientStructureChangeRegistry::getDb()->createCommand('LOCK TABLE ' . $table);

        $data = $this->getData();

        if ($data) {
            $this->truncateData();
        }
        ClientStructureChangeRegistry::getDb()->createCommand('UNLOCK TABLE ' . $table);

        return $data;
    }

    public function anonce($superId)
    {
        return EbcKafka::me()->sendMessage(
            self::TOPIC,
            ClientSuperDao::me()->getSuperClientStructByIds([$superId]),
            (string)$superId,
            [
                'uuid' => Utils::genUUID()
            ],
        );
    }
}
