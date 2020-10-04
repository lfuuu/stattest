<?php

namespace app\forms\client;

use app\classes\behaviors\HistoryChanges;
use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use yii\base\Component;

class ClientAccountOptionsSaveModel extends Component
{
    public $_data = [];
    public $_oldData = [];
    public $_clientAccountId = null;
    public $_isNewRecord = false;

    public $primaryKey = null;

    public function behaviors()
    {
        return [
            'HistoryChanges' => HistoryChanges::class, // Логирование изменений всегда в конце
        ];
    }

    public function setClientAccount($clientAccountId)
    {
        $this->_clientAccountId = $clientAccountId;
        $this->primaryKey = $clientAccountId;

        return $this;
    }

    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    public function setOldData($data)
    {
        $this->_oldData = $data;

        return $this;
    }

    public function save($isNewRecord)
    {
        $transaction = ClientAccountOptions::getDb()->beginTransaction();
        try {
            $rows = $this->_data;
            ClientAccountOptions::deleteAll(['client_account_id' => $this->_clientAccountId, 'option' => array_keys($rows)]);

            array_walk($rows,
                function (&$value, $key) {
                    $value = [$this->_clientAccountId, $key, $value];
                });
            ClientAccountOptions::getDb()->createCommand()
                ->batchInsert(ClientAccountOptions::tableName(), ['client_account_id', 'option', 'value'], $rows)
                ->execute();

            $this->trigger($isNewRecord ? ActiveRecord::EVENT_AFTER_INSERT : ActiveRecord::EVENT_BEFORE_UPDATE);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function delete()
    {
        $this->trigger(ActiveRecord::EVENT_AFTER_DELETE);
    }

    public function getAttributes()
    {
        return $this->_data + ['client_account_id' => $this->_clientAccountId];
    }

    public function getOldAttributes()
    {
        return $this->_oldData + ['client_account_id' => $this->_clientAccountId];
    }

    // мимикрируем под ClientAccount
    public function getClassName()
    {
        return ClientAccount::class;
    }

    public function getParentId()
    {
        return null;
    }
}