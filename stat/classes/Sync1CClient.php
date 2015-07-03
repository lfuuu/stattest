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
        $account = \app\models\ClientAccount::findOne(['client' => $cl_main_card]);
        if (!$this->saveClientCard($account->id)) {
            return false;
        }
        return true;
    }

    public function saveClientCard($clientCardId)
    {
        $account = \app\models\ClientAccount::findOne($clientCardId);
        if (!$account)
            return false;

        global $user;

        try {
            $params = array(
                'contract' => array(
                    'ИдКлиентаСтат' => $account->client,
                    'ИдКарточкиКлиентаСтат' => $account->client,
                    'КодКлиентаСтат' => $account->id,
                    'КодКарточкиКлиентаСтат' => $account->id,
                    'НаименованиеКомпании' => $account->contract->contragent->name,
                    'ПолноеНаименованиеКомпании' => $account->contract->contragent->name_full,
                    'ИНН' => $account->contract->contragent->inn,
                    'КПП' => $account->contract->contragent->kpp,
                    'ЮридическийАдрес' => $account->contract->contragent->address_jur,
                    'ПравоваяФорма' => in_array($account->contract->contragent->legal_type, ['legal', 'ip']) ? 'ЮрЛицо' : 'ФизЛицо',
                    'Организация' => $account->contract->organization,
                    'ВалютаРасчетов' => $account->currency,
                    'ВидЦен' => $account->price_type ? $account->price_type: '739a53ba-8389-11df-9af5-001517456eb1'
                ),
                'Пользователь' => $user->Get("user")
            );

            $this->soap->utSaveClientContract($params);

        } catch (\SoapFault $e) {
            $this->helper->throw1CException($e);
        }
        Yii::$app->db->createCommand("delete from z_sync_1c where tname='clientCard' and tid='$account->id'")->execute();

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
