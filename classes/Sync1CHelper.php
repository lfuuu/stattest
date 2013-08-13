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
            'éÄëÌÉÅÎÔÁóÔÁÔ' => $client->client,
            'éÄëÁÒÔÏŞËÉëÌÉÅÎÔÁóÔÁÔ' => $clientCard->client,
            'ëÏÄëÌÉÅÎÔÁóÔÁÔ' => $client->id,
            'ëÏÄëÁÒÔÏŞËÉëÌÉÅÎÔÁóÔÁÔ' => $clientCard->id,
            'îÁÉÍÅÎÏ×ÁÎÉÅëÏÍĞÁÎÉÉ' => $clientCard->company,
            'ğÏÌÎÏÅîÁÉÍÅÎÏ×ÁÎÉÅëÏÍĞÁÎÉÉ' => $clientCard->company_full,
            'éîî' => $clientCard->inn,
            'ëğğ' => $clientCard->kpp,
            'ğÒÁ×Ï×ÁÑæÏÒÍÁ' => $clientCard->type == 'org' ? 'àÒìÉÃÏ' : 'æÉÚìÉÃÏ',
            'ïÒÇÁÎÉÚÁÃÉÑ' => $clientCard->firma,
            '÷ÁÌÀÔÁòÁÓŞÅÔÏ×' => $clientCard->currency,
            '÷ÉÄãÅÎ' => $clientCard->price_type ? $clientCard->price_type: '739a53ba-8389-11df-9af5-001517456eb1'
        );

        return $clientCardData;
    }




	public function throw1CException($e)
	{
		$messages = explode("|||",Encoding::toKoi8r($e->getMessage()));
		throw new Sync1CException(count($messages) > 1 ? $messages[1] : $messages[0]);
	}

}