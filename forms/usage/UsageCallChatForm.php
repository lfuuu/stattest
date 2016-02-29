<?php
namespace app\forms\usage;

use app\classes\Form;

class UsageCallChatForm extends Form
{
    public $id;
    public $client;
    public $actual_from;
    public $actual_to;
    public $tarif_id;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['actual_from', 'actual_to',], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'client' => 'Клиент',
            'actual_from' => 'Дата подключения',
            'actual_to' => 'Дата отключения',
            'tarif_id' => 'Тариф',
            'status' => 'Статус услуги',
            'comment' => 'Название чата'
        ];
    }

}