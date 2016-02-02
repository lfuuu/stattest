<?php
namespace app\forms\client;

use Yii;
use app\classes\Form;
use app\models\ClientAccountOptions;

class ClientAccountOptionsForm extends Form
{

    public
        $client_account_id,
        $option,
        $value;

    public function rules()
    {
        return [
            [['client_account_id',], 'integer'],
            [['client_account_id',], 'required'],
            [['option',], 'string'],
        ];
    }

    public function setClientAccountId($clientAccountId)
    {
        $this->client_account_id = $clientAccountId;
        return $this;
    }

    public function setOption($option)
    {
        $this->option = $option;
        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function save()
    {
        if ($this->validate()) {
            $record = new ClientAccountOptions;
            $record->setAttributes($this->getAttributes());
            $record->save();
        }
    }

}