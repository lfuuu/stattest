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
    public static function tableName()
    {
        return 'client_contract_type';
    }
}
