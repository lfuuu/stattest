<?php

class Sync1CClient
{
    protected $handler;
    protected $soap;
    /**
     * @var Sync1CHelper
     */
    protected $helper;

    public function __construct($soapHandler, $helper)
    {
        $this->helper = $helper;
        $this->soap = $soapHandler;
    }


    public function saveClientCards($cl_main_card)
    {
        global $db;
        $cls = $db->AllRecords("select id from clients where client ='" . addcslashes($cl_main_card, "\\'") . "' or client like '" . addcslashes($cl_main_card, "\\'") . "/_'", null, \MYSQL_ASSOC);
        foreach ($cls as $cl) {
            if (!$this->saveClientCard($cl['id'])) {
                return false;
            }
        }
        return true;
    }

    public function saveClientCard($clientCardId)
    {
        $clientCard = ClientCard::find($clientCardId);
        if (!$clientCard)
            return false;

        if ($clientCard->type == 'office')
            return true;

        $client = $clientCard->getClient();

        global $user;

        try {
            $params = array(
                'contract' => array(
                    'ИдКлиентаСтат' => $client->client,
                    'КодКлиентаСтат' => $client->id,
                    'ИдКарточкиКлиентаСтат' => $clientCard->client,
                    'КодКарточкиКлиентаСтат' => $clientCard->id,
                    'НаименованиеКомпании' => $clientCard->company,
                    'ПолноеНаименованиеКомпании' => $clientCard->company_full,
                    'ИНН' => $clientCard->inn,
                    'КПП' => $clientCard->kpp,
                    'ЮридическийАдрес' => $clientCard->address_jur,
                    'ПравоваяФорма' => $clientCard->type,
                    'Организация' => $clientCard->firma,
                    'ВалютаРасчетов' => $clientCard->currency,
                    'ВидЦен' => $clientCard->price_type ? $clientCard->price_type : '739a53ba-8389-11df-9af5-001517456eb1',
                ),
                'Пользователь' => $user->Get("user")
            );

            $this->soap->utSaveClientContract($params);

        } catch (\SoapFault $e) {
            $this->helper->printSoapFault($e);
            return false;
        }

        $clientCard->markSync(false);

        return true;
    }

    /*
    public function __call($method, $args){
        return call_user_func_array(array($this->handler, $method), $args);
    }
    */
}
