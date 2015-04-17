<?php
namespace app\forms\usage;

use app\classes\Form;

class UsageTrunkForm extends Form
{
    public $id;
    public $client_account_id;
    public $connection_point_id;
    public $trunk_name;
    public $actual_from;
    public $actual_to;

    public function rules()
    {
        return [
            [['id','client_account_id','connection_point_id'], 'integer'],
            [['trunk_name','actual_from', 'actual_to'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'connection_point_id' => 'Точка присоединения',
            'connecting_date' => 'Дата подключения',
            'tariff_change_date' => 'Дата изменения тарифа',
            'trunk_name' => 'Имя транка',
            'actual_from' => 'Дата подключения',
            'actual_to' => 'Дата отключения',
        ];
    }

}