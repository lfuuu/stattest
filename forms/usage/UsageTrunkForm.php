<?php

namespace app\forms\usage;

use app\classes\Form;
use app\models\UsageTrunk;

class UsageTrunkForm extends Form
{
    public
        $id,
        $client_account_id,
        $connection_point_id,
        $trunk_id,
        $actual_from,
        $actual_to,
        $orig_enabled,
        $term_enabled,
        $orig_min_payment,
        $term_min_payment,
        $description,
        $ip,
        $transit_price;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'client_account_id', 'connection_point_id', 'trunk_id',], 'integer'],
            [['actual_from', 'actual_to', 'description'], 'string'],
            ['ip', 'ip'],
            [['orig_enabled', 'term_enabled', 'orig_min_payment', 'term_min_payment'], 'integer'],
            ['transit_price', 'number'],
        ];
    }

    /**
     * @return array
     */
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
            'ip' => 'IP-адрес',
            'transit_price' => 'Цена транзита',
        ];
    }

    /**
     * @return UsageTrunk
     */
    public function getModel()
    {
        return $this->usage;
    }

}