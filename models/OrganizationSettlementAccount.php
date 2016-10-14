<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $organization_record_id
 * @property int $settlement_account_type_id
 * @property string $bank_name
 * @property string $bank_address
 * @property string $bank_correspondent_account
 * @property string $bank_bik
 * @property string $bank_account
 */
class OrganizationSettlementAccount extends ActiveRecord
{

    const SETTLEMENT_ACCOUNT_TYPE_RUSSIA = 1;
    const SETTLEMENT_ACCOUNT_TYPE_SWIFT = 2;
    const SETTLEMENT_ACCOUNT_TYPE_IBAN = 3;

    public static $typesList = [
        self::SETTLEMENT_ACCOUNT_TYPE_RUSSIA => 'Реквизиты счёта в российском банке',
        self::SETTLEMENT_ACCOUNT_TYPE_SWIFT => 'SWIFT реквизиты',
        self::SETTLEMENT_ACCOUNT_TYPE_IBAN => 'IBAN реквизиты',
    ];

    public static $currencyBySettlementAccountTypeId = [
        self::SETTLEMENT_ACCOUNT_TYPE_RUSSIA => [
            Currency::RUB, Currency::USD, Currency::EUR,
        ],
        self::SETTLEMENT_ACCOUNT_TYPE_SWIFT => [
            Currency::HUF, Currency::USD, Currency::EUR,
        ],
        self::SETTLEMENT_ACCOUNT_TYPE_IBAN => [
            Currency::HUF, Currency::USD, Currency::EUR,
        ],
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'organization_settlement_account';
    }

    /**
     * @return string[]
     */
    public static function primaryKey()
    {
        return ['organization_record_id', 'settlement_account_type_id'];
    }

    /**
     * @return OrganizationSettlementAccountProperties[]
     */
    public function getProperties()
    {
        return
            $this->hasMany(OrganizationSettlementAccountProperties::className(), [
                'organization_record_id' => 'organization_record_id',
                'settlement_account_type_id' => 'settlement_account_type_id',
            ])
                ->select(['property', 'value'])
                ->indexBy('property');
    }

    /**
     * @param string $propertyName
     * @return OrganizationSettlementAccountProperties
     */
    public function getProperty($propertyName)
    {
        $property = OrganizationSettlementAccountProperties::findOne([
            'organization_record_id' => $this->organization_record_id,
            'settlement_account_type_id' => $this->settlement_account_type_id,
            'property' => $propertyName,
        ]);

        return $property ?: new OrganizationSettlementAccountProperties;
    }

    /**
     * THIS IS BLACK MAGIC (replace unknown ActiveRecord property)
     * @return string
     */
    public function getBank_account()
    {
        $defaultCurrency = reset(self::$currencyBySettlementAccountTypeId[$this->settlement_account_type_id]);
        return (string)$this->getProperty('bank_account_' . $defaultCurrency);
    }

}