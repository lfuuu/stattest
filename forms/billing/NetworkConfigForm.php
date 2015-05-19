<?php
namespace app\forms\billing;

use app\classes\Assert;
use app\classes\Form;
use app\models\billing\NetworkConfig;

class NetworkConfigForm extends Form
{
    public $id;
    public $connection_point_id;
    public $name;

    public function rules()
    {
        return [
            [['id','connection_point_id'], 'integer'],
            [['name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'connection_point_id' => 'Точка присоединения',
            'local_network_config_id' => 'Местные префиксы',
            'name' => 'Название',
        ];
    }

    public function save()
    {
        if ($this->id) {
            $networkConfig = NetworkConfig::findOne($this->id);
            Assert::isObject($networkConfig);
        } else {
            $networkConfig = new NetworkConfig();
            $networkConfig->connection_point_id = $this->connection_point_id;
        }
        $networkConfig->name = $this->name;

        $networkConfig->save();

        $this->id = $networkConfig->id;

        return true;
    }
}