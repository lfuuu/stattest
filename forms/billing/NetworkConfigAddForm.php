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
        $rules[] = [['name','connection_point_id'], 'required'];
        return $rules;
    }

    public function save()
    {
        $networkConfig = new NetworkConfig();
        $networkConfig->instance_id = $this->connection_point_id;
        $networkConfig->name = $this->name;
        $networkConfig->geo_city_id = $this->geo_city_id;
        $networkConfig->geo_operator_id = $this->geo_operator_id;

        $networkConfig->save();
        $this->id = $networkConfig->id;

        return true;
    }
}