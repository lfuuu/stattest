<?php
namespace app\forms\client;

use app\exceptions\ModelValidationException;
use Yii;
use app\classes\Form;
use app\models\ClientAccountOptions;
use yii\base\Exception;

class ClientAccountOptionsForm extends Form
{

    public
        $client_account_id,
        $option,
        $value;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['client_account_id',], 'integer'],
            [['client_account_id',], 'required'],
            [['option', 'value',], 'string'],
        ];
    }

    /**
     * @param int $clientAccountId
     * @return $this
     */
    public function setClientAccountId($clientAccountId)
    {
        $this->client_account_id = $clientAccountId;
        return $this;
    }

    /**
     * @param string $option
     * @return $this
     */
    public function setOption($option)
    {
        $this->option = $option;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param bool|true $deleteExisting
     * @return bool
     * @throws \Exception
     */
    public function save($deleteExisting = true)
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            if ($deleteExisting === true) {
                ClientAccountOptions::deleteAll([
                    'and',
                    ['client_account_id' => $this->client_account_id],
                    ['option' => $this->option]
                ]);
            }

            $record = new ClientAccountOptions;
            $record->setAttributes($this->getAttributes());

            if (!$record->save()) {
                throw new ModelValidationException($record);
            }

            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}