<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $organization_record_id
 * @property int $settlement_account_type_id
 * @property string $property
 * @property string $value
 */
class OrganizationSettlementAccountProperties extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'organization_settlement_account_properties';
    }

    /**
     * @return array
     */
    public static function primaryKey()
    {
        return ['organization_record_id', 'settlement_account_type_id', 'property'];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

}