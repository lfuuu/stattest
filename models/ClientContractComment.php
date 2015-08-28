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

    const SET_BUSINESS = 'Установлено подразделение: ';
    const SET_BUSINESS_PROCESS = 'Установлен бизнес процесc: ';
    const SET_BUSINESS_PROCESS_STATUS = 'Установлен статус бизнес процесса: ';
    const SET_CLIENT_BLOCKED_TRUE = 'Лицевой счет заблокирован';
    const SET_CLIENT_BLOCKED_FALSE = 'Лицевой счет разблокирован';
    const SET_CLIENT_ACTIVE_TRUE = 'Лицевой счет открыт';
    const SET_CLIENT_ACTIVE_FALSE = 'Лицевой счет закрыт';

    public static function tableName()
    {
        return 'client_contract_comment';
    }

}
