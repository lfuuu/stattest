<?php

namespace app\modules\transfer\forms\contragent;

use app\classes\api\ApiCore;
use app\classes\Assert;
use app\classes\Form;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\ClientSuper;
use Yii;
use yii\base\Exception;
use yii\base\InvalidCallException;
use yii\base\ModelEvent;

class BaseForm extends Form
{

    /** @var ClientAccount */
    public $sourceClientAccount;

    /** @var ClientAccount */
    public $targetClientAccount;

    /** @var array */
    public $contracts = [];

    /** @var array */
    public $clients = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['targetClientAccount', 'sourceClientAccount',], 'required'],
            [['targetClientAccount', 'sourceClientAccount',], 'integer'],
            [['contracts', 'clients',], ArrayValidator::class],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'targetClientAccount' => 'Супер клиент',
            'sourceClientAccount' => 'ID супер клиента',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\ClientContragent::class,
        ];
    }

    /**
     * @return bool
     * @throws Exception
     * @throws \yii\db\Exception
     * @throws InvalidCallException
     */
    public function process()
    {
        /** @var ClientContragent $contragent */
        $contragent = ClientContragent::findOne($this->sourceClientAccount);
        Assert::isObject($contragent);

        $superClient = ClientSuper::findOne($contragent->super_id);
        Assert::isObject($superClient);

        $contracts = ClientContract::findAll(['id' => $this->contracts]);
        $clients = ClientAccount::findAll(['id' => $this->clients]);

        try {
//            ApiCore::transferContragent($contragent->id, $superClient->id, $this->targetClientAccount->id);
        } catch (Exception $e) {
            $this->addError('transfer-error', 'API: ' . $e->getMessage());
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($clients as $client) {
                $client->super_id = $this->targetClientAccount;
                if (!$client->save()) {
                    throw new ModelValidationException($client);
                }
            }

            foreach ($contracts as $contract) {
                $contract->super_id = $this->targetClientAccount;
                if (!$contract->save()) {
                    throw new ModelValidationException($contract);
                }
            }

            $contragent->super_id = $this->targetClientAccount;
            if (!$contragent->save($runValidation = false)) {
                throw new ModelValidationException($contragent);
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->addError('transfer-error', $e->getMessage());
            return false;
        }

//        $this->trigger(static::EVENT_AFTER_SAVE, new ModelEvent);

        return true;
    }

}