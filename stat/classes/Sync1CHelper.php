<?php

class Sync1CHelper
{
    public function getClientCardData($cardId)
    {
        $client = \app\models\ClientAccount::findOne($cardId);
        if (!$client)
            throw new Exception('Client not found');

        $clientCardData = array(
            'ИдКлиентаСтат' => $client->client,
            'ИдКарточкиКлиентаСтат' => $client->client,
            'КодКлиентаСтат' => $client->id,
            'КодКарточкиКлиентаСтат' => $client->id,
            'НаименованиеКомпании' => $client->contract->contragent->name,
            'ПолноеНаименованиеКомпании' => $client->contract->contragent->name_full,
            'ИНН' => $client->contract->contragent->inn,
            'КПП' => $client->contract->contragent->kpp,
            'ПравоваяФорма' => in_array($client->contract->contragent->legal_type, ['legal', 'ip']) ? 'ЮрЛицо' : 'ФизЛицо',
            'Организация' => $client->contract->organization,
            'ВалютаРасчетов' => $client->currency,
            'ВидЦен' => $client->price_type ? $client->price_type: '739a53ba-8389-11df-9af5-001517456eb1'
        );

        return $clientCardData;
    }




    public function throw1CException($e)
    {
        $messages = explode("|||",$e->getMessage());
        throw new Sync1CException(count($messages) > 1 ? $messages[1] : $messages[0]);
    }

}