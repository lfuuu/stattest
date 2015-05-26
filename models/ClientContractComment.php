<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class ClientContractComment extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contract_comment';
    }
}
