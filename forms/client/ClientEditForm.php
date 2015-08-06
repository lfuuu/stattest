<?php
namespace app\forms\client;

use app\classes\Form;
use app\models\ClientSuper;

class ClientEditForm extends Form
{
    public $id,
        $name,
        $financial_manager_id;

    protected $super = null;

    //public $ddate = null;

    public function rules()
    {
        $rules = [
            [['name'], 'string'],
            [['financial_manager_id'], 'integer'],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return (new ClientSuper())->attributeLabels();
    }

    public function init()
    {
        if ($this->id) {
            $this->super = ClientSuper::findOne($this->id);
            if ($this->super === null) {
                throw new Exception('SuperClient not found');
            }
            $this->setAttributes($this->super->getAttributes(), false);
        } else
            throw new Exception('You must send id');
    }

    public function save()
    {
        $super = $this->super;
        $super->setAttributes(array_filter($this->getAttributes()), false);

        if ($super->save()) {
            $this->setAttributes($super->getAttributes(), false);
            return true;
        } else
            $this->addErrors($super->getErrors());

        return false;
    }

    public function getIsNewRecord()
    {
        return $this->id ? false : true;
    }
}