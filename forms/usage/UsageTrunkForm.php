<?php
namespace app\forms\usage;

use app\classes\Form;

class UsageTrunkForm extends Form
{
    public $id;
    public $client_account_id;
    public $connection_point_id;
    public $trunk_id;
    public $actual_from;
    public $actual_to;
    public $orig_enabled;
    public $term_enabled;
    public $orig_min_payment;
    public $term_min_payment;
    public $description;

    public function rules()
    {
        return [
            [['id','client_account_id','connection_point_id','trunk_id'], 'integer'],
            [['actual_from', 'actual_to','description'], 'string'],
            [['orig_enabled','term_enabled', 'orig_min_payment','term_min_payment'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'connection_point_id' => 'Точка присоединения',
            'connecting_date' => 'Дата подключения',
            'tariff_change_date' => 'Дата изменения тарифа',
            'trunk_id' => 'Транк',
            'actual_from' => 'Дата подключения',
            'actual_to' => 'Дата отключения',
            'orig_enabled' => 'Оригинация включена',
            'term_enabled' => 'Терминация включена',
            'orig_min_payment' => 'Минимальный платеж за оригинацию',
            'term_min_payment' => 'Минимальный платеж за терминацию',
            'description' => 'Описание',
        ];
    }

}