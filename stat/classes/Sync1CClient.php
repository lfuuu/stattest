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
        $cls = \app\models\ClientAccount::find(['client' => $cl_main_card])->all();
        foreach ($cls as $cl) {
            if (!$this->saveClientCard($cl->id)) {
                return false;
            }
        }
        return true;
    }

    public function saveClientCard($clientCardId)
    {
        $client = \app\models\ClientAccount::findOne($clientCardId);
        if (!$client)
            return false;

        global $user;

        try {
            $params = array(
                'contract' => array(
                    'ИдКлиентаСтат' => $client->client,
                    'ИдКарточкиКлиентаСтат' => $client->client,
                    'КодКлиентаСтат' => $client->id,
                    'КодКарточкиКлиентаСтат' => $client->id,
                    'НаименованиеКомпании' => $client->contract->contragent->name,
                    'ПолноеНаименованиеКомпании' => $client->contract->contragent->name_full,
                    'ИНН' => $client->contract->contragent->inn,
                    'КПП' => $client->contract->contragent->kpp,
                    'ЮридическийАдрес' => $client->contract->contragent->address_jur,
                    'ПравоваяФорма' => in_array($client->contract->contragent->legal_type, ['legal', 'ip']) ? 'ЮрЛицо' : 'ФизЛицо',
                    'Организация' => $client->contract->organization,
                    'ВалютаРасчетов' => $client->currency,
                    'ВидЦен' => $client->price_type ? $client->price_type: '739a53ba-8389-11df-9af5-001517456eb1'
                ),
                'Пользователь' => $user->Get("user")
            );

            $this->soap->utSaveClientContract($params);

        } catch (\SoapFault $e) {
            $this->helper->throw1CException($e);
        }
        Yii::$app->db->createCommand("delete from z_sync_1c where tname='clientCard' and tid='$client->id'")->execute();

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
