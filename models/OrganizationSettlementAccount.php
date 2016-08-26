<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $person_id
 * @property string $lang_code
 * @property string $field
 * @property string $value
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

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'organization_settlement_account';
    }

}