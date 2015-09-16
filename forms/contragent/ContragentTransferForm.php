<?php

namespace app\forms\contragent;

use Yii;
use app\classes\Assert;
use app\classes\Form;
use app\models\ClientContragent;
use app\models\ClientContract;
use app\models\ClientAccount;

class ContragentTransferForm extends Form
{

    public
        $sourceClientAccount,
        $targetClientAccount,
        $contracts = [],
        $clients = [];

    public function rules()
    {
        return [
            [['targetClientAccount','sourceClientAccount','contracts','clients',], 'required'],
            [['targetClientAccount','sourceClientAccount',], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'targetClientAccount' => 'Супер клиент',
            'sourceClientAccount' => 'ID супер клиента',
        ];
    }

    /**
     * Процесс переноса
     */
    public function process()
    {
        $contragent = ClientContragent::findOne($this->sourceClientAccount);
        Assert::isObject($contragent);

        $contracts = ClientContract::find()->where(['id' => $this->contracts])->all();
        Assert::isArray($contracts);

        $clients = ClientAccount::find()->where(['id' => $this->clients])->all();
        Assert::isArray($clients);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($clients as $client) {
                $client->super_id = $this->targetClientAccount;
                $client->save();
            }

            foreach ($contracts as $contract) {
                $contract->super_id = $this->targetClientAccount;
                $contract->save();
            }

            $contragent->super_id = $this->targetClientAccount;
            $contragent->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}