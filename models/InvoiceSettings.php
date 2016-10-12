<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $customer_country_code
 * @property int $doer_country_code
 * @property int $settlement_account_type_id
 * @property int $vat_rate
 * @property int $contragent_type
 */
class InvoiceSettings extends ActiveRecord
{

    const CONTRAGENT_TYPE_DEFAULT = '*';

    public static $contragentTypes = [
        self::CONTRAGENT_TYPE_DEFAULT => 'Для всех',
        ClientContragent::LEGAL_TYPE => 'Для юр. лиц',
        ClientContragent::PERSON_TYPE => 'Для физ. лиц',
        ClientContragent::IP_TYPE => 'Для ИП',
    ];

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
     * @return array
     */
    public function rules()
    {
        return [
            [['customer_country_code', 'doer_country_code', 'settlement_account_type_id', 'vat_rate'], 'integer'],
            [['customer_country_code', 'doer_country_code', 'settlement_account_type_id'], 'required'],
            ['contragent_type', 'string'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'customer_country_code' => 'Страна заказчика',
            'doer_country_code' => 'Страна исполнителя',
            'settlement_account_type_id' => 'Тип платежных реквизитов',
            'vat_rate' => 'Ставка налога',
            'contragent_type' => 'Тип клиента',
        ];
    }

}