<?php

namespace app\forms\tariff\call_chat;

use app\classes\Form;
use app\models\TariffCallChat;
use app\models\TariffVoipPackage;

class CallChatForm extends Form
{

    public
        $id,
        $description = '',
        $price = 0,
        $currency_id = 0,
        $price_include_vat = 1,
        $status = 'public';


    public function rules()
    {
        return [
            [['price', 'description', 'currency_id'], 'required'],
            [['price'], 'double'],
            [['description', 'status', 'currency_id'], 'string'],
            [['price_include_vat'], 'boolean']
        ];
    }

    public function attributeLabels()
    {
        return [
            'description' => 'Название',
            'price' => 'Цена',
            'currency_id' => 'Валюта',
            'destination_id' => 'Направление',
            'price_include_vat' => 'включить в цену ставку налога',
            'status' => 'Статус тарифа',
            'edit_user' => 'Редактор',
            'edit_time' => 'Время редактирования'
        ];
    }

    public function save(TariffCallChat $tariff = null)
    {

        if ($tariff === null) {
            $tariff = new TariffCallChat;
        }
        $tariff->setAttributes($this->getAttributes(), false);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $tariff->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->id = $tariff->id;

        return true;
    }

}