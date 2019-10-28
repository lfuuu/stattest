<?php

namespace app\forms\client;

use app\classes\helpers\ArrayHelper;
use app\classes\Singleton;
use app\models\ClientAccountOptions;

class ClientAccountOptionsSaveForm
{

    private $_options = [];

    public function addOptionForm(ClientAccountOptionsForm $form)
    {
        $this->_options[] = $form;
    }

    public function save($deleteExisting = true)
    {
        $data = [];

        /** @var ClientAccountOptionsForm $form */
        foreach ($this->_options as $form) {
            $data[$form->client_account_id][$form->option] = $form->value;
        }

        foreach ($data as $clientAccountId => $clientData) {
            $this->_saveClientAccount($clientAccountId, $clientData, $deleteExisting);
        }
    }

    private function _load($clientAccountId)
    {
        return ClientAccountOptions::find()
            ->where(['client_account_id' => $clientAccountId])
            ->select('value')
            ->indexBy('option')
            ->orderBy(['option' => SORT_ASC])
            ->asArray()
            ->column();
    }

    private function _saveClientAccount($clientAccountId, $clientData)
    {
        $dbData = $this->_load($clientAccountId);

        $isNewRecord = !((bool)$dbData);

        $data = ArrayHelper::merge($dbData, $clientData);

        (new ClientAccountOptionsSaveModel())
            ->setClientAccount($clientAccountId)
            ->setOldData($dbData)
            ->setData($data)
            ->save($isNewRecord);
    }
}