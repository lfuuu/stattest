<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property varchar $name
 * @property int $sort
 * @property
 */
class ClientContractType extends ActiveRecord
{
    const TELEKOM = 2;
    const OPERATOR = 3;
    const SHOP = 5;
    const INTERNAL_OFFICE = 6;

    public static function tableName()
    {
        return 'client_contract_type';
    }
}
