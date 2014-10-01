<?php

class Sync1CClient
{
    protected $handler;
    protected $soap;
    /**
     * @var Sync1CHelper
     */
    protected $helper;

    public function __construct($soapHandler)
    {
        $this->helper = new Sync1CHelper();
        $this->soap = $soapHandler;
        $this->handler = new Sync1CServerHandler($this->helper);
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
            $this->helper->throw1CException($e);
        }

        $clientCard->markSync(false);

        return true;
    }

    public function saveGtd(array $data)
    {
        global $user;

        try {
            $params = array(
                'gtd' => $data,
                'user' => $user->Get("user")
            );

            $result = $this->soap->utSaveGtd($params);

        } catch (\SoapFault $e) {
            $this->helper->throw1CException($e);
        }

        return $this->handler->parseGtd($result->return);
    }

    public function saveGoodsIncomeOrder(array $data)
    {
        global $user;

        try {
            $params = array(
                'order' => $data,
                'user' => $user->Get("user")
            );

            $result = $this->soap->utSaveGoodsIncomeOrder($params);

        } catch (\SoapFault $e) {
            $this->helper->throw1CException($e);
        }

        return $this->handler->parseGoodsIncomeOrder($result->return);
    }

    public function saveGoodsIncomeDocument(array $data)
    {
        global $user;

        try {
            $params = array(
                'document' => $data,
                'user' => $user->Get("user")
            );

            $result = $this->soap->utSaveGoodsIncomeDocument($params);

        } catch (\SoapFault $e) {
            $this->helper->throw1CException($e);
        }

        return $this->handler->parseGoodsIncomeDocument($result->return);
    }

    /*
    public function __call($method, $args){
        return call_user_func_array(array($this->handler, $method), $args);
    }
    */
}
