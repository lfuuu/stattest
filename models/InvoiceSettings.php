<?php
namespace app\models;

use yii\db\ActiveRecord;

class InvoiceSettings extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'invoice_settings';
    }

    /**
     * @return array
     */
    public static function primaryKey()
    {
        return ['customer_country_code', 'doer_country_code', 'settlement_account_type_id'];
    }

    /**
     * @return []
     */
    public function rules()
    {
        return [
            [['customer_country_code', 'doer_country_code', 'settlement_account_type_id', 'vat_rate'], 'integer'],
            [['customer_country_code', 'doer_country_code', 'settlement_account_type_id'], 'required'],
        ];
    }

    /**
     * @return []
     */
    public function attributeLabels()
    {
        return [
            'customer_country_code' => 'Страна заказчика',
            'doer_country_code' => 'Страна исполнителя',
            'settlement_account_type_id' => 'Тип платежных реквизитов',
            'vat_rate' => 'Ставка налога',
        ];
    }

}