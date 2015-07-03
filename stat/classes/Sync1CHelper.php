<?php

class Sync1CHelper
{
    public function getClientCardData($cardId)
    {
        $account = \app\models\ClientAccount::findOne($cardId);
        if (!$account)
            throw new Exception('Client not found');

        $clientCardData = array(
            'ИдКлиентаСтат' => $account->client,
            'ИдКарточкиКлиентаСтат' => $account->client,
            'КодКлиентаСтат' => $account->id,
            'КодКарточкиКлиентаСтат' => $account->id,
            'НаименованиеКомпании' => $account->contract->contragent->name,
            'ПолноеНаименованиеКомпании' => $account->contract->contragent->name_full,
            'ИНН' => $account->contract->contragent->inn,
            'КПП' => $account->contract->contragent->kpp,
            'ПравоваяФорма' => in_array($account->contract->contragent->legal_type, ['legal', 'ip']) ? 'ЮрЛицо' : 'ФизЛицо',
            'Организация' => $account->contract->organization,
            'ВалютаРасчетов' => $account->currency,
            'ВидЦен' => $account->price_type ? $account->price_type: '739a53ba-8389-11df-9af5-001517456eb1'
        );

        return $clientCardData;
    }




    public function throw1CException($e)
    {
        $messages = explode("|||",$e->getMessage());
        throw new Sync1CException(count($messages) > 1 ? $messages[1] : $messages[0]);
    }

}