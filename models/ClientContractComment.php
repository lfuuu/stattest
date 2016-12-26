<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class ClientContractComment
 *
 * @property int $id
 * @property string $comment
 * @property string $user
 * @property string $ts
 * @property int $is_publish
 */
class ClientContractComment extends ActiveRecord
{

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_contract_comment';
    }

}
