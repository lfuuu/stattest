<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $comment
 * @property string $user
 * @property string $ts
 * @property int $is_publish
 * @property
 */
class ClientContractComment extends ActiveRecord
{

    public static function tableName()
    {
        return 'client_contract_comment';
    }

}
