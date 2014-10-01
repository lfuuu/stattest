<?php

class Sync1CHelper
{
    public function getClientCardData($cardId)
    {
        $clientCard = ClientCard::find($cardId);
        if (!$clientCard)
            throw new Exception('Client card not found');

        $client = $clientCard->getClient();
        if (!$client)
            throw new Exception('Client not found');

        $clientCardData = array(
            'ИдКлиентаСтат' => $client->client,
            'ИдКарточкиКлиентаСтат' => $clientCard->client,
            'КодКлиентаСтат' => $client->id,
            'КодКарточкиКлиентаСтат' => $clientCard->id,
            'НаименованиеКомпании' => $clientCard->company,
            'ПолноеНаименованиеКомпании' => $clientCard->company_full,
            'ИНН' => $clientCard->inn,
            'КПП' => $clientCard->kpp,
            'ПравоваяФорма' => $clientCard->type == 'org' ? 'ЮрЛицо' : 'ФизЛицо',
            'Организация' => $clientCard->firma,
            'ВалютаРасчетов' => $clientCard->currency,
            'ВидЦен' => $clientCard->price_type ? $clientCard->price_type: '739a53ba-8389-11df-9af5-001517456eb1'
        );

        return $clientCardData;
    }




    public function throw1CException($e)
    {
        $messages = explode("|||",$e->getMessage());
        throw new Sync1CException(count($messages) > 1 ? $messages[1] : $messages[0]);
    }

}