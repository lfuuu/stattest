<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\dao\InvoiceSettingsDao;

/**
 * @property int $doer_organization_id
 * @property int $customer_country_code
 * @property int $vat_apply_scheme
 * @property int $settlement_account_type_id
 * @property int $vat_rate
 * @property string $at_account_code
 * @property string $tax_reason
 */
class InvoiceSettings extends ActiveRecord
{

    const VAT_SCHEME_ANY = 1;
    const VAT_SCHEME_NONVAT = 2;
    const VAT_SCHEME_VAT = 3;

    public static $vatApplySchemes = [
        self::VAT_SCHEME_ANY => 'Любой',
        self::VAT_SCHEME_NONVAT => 'УСН (NonVAT)',
        self::VAT_SCHEME_VAT => 'ОСН (VAT)',
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
        return ['doer_organization_id', 'customer_country_code', 'settlement_account_type_id', 'vat_apply_scheme',];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['doer_organization_id', 'customer_country_code', 'settlement_account_type_id', 'vat_rate', 'vat_apply_scheme', 'at_account_code', ],
                'integer'
            ],
            ['tax_reason', 'string'],
            [['doer_organization_id', 'settlement_account_type_id'], 'required'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'doer_organization_id' => 'Компания оказывающая услуги',
            'customer_country_code' => 'Страна получающая услугу',
            'vat_apply_scheme' => 'Схема применения налоговой ставки',
            'vat_rate' => 'Ставка налога',
            'settlement_account_type_id' => 'Тип платежных реквизитов',
            'at_account_code' => 'Номер счета согласно Бухгалтерскому план счетов Австрии',
            'tax_reason' => 'Tax reason (EU)',
        ];
    }

    public static function dao()
    {
        return InvoiceSettingsDao::me();
    }

}