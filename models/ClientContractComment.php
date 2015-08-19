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

    const SET_CONTRACT_TYPE = 'Установлен тип договора: ';
    const SET_BUSINESS_PROCESS = 'Установлен бизнес процесc: ';
    const SET_BUSINESS_PROCESS_STATUS = 'Установлен статус бизнес процесса: ';

    public static function tableName()
    {
        return 'client_contract_comment';
    }

}
