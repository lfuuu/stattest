<?php
namespace app\forms\billing;

use app\classes\Assert;
use app\classes\Form;
use app\models\billing\NetworkConfig;

class NetworkConfigAddForm extends NetworkConfigForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name','connection_point_id','pricelist_id'], 'required'];
        return $rules;
    }

    public function save()
    {
        $networkConfig = new NetworkConfig();
        $networkConfig->connection_point_id = $this->connection_point_id;
        $networkConfig->name = $this->name;
        $networkConfig->pricelist_id = $this->pricelist_id;

        $networkConfig->save();
        $this->id = $networkConfig->id;

        return true;
    }
}