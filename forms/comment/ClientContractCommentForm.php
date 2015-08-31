<?php

namespace app\forms\comment;

use Yii;
use DateTime;
use app\classes\Form;
use app\models\ClientContractComment;

class ClientContractCommentForm extends Form
{

    const SET_BUSINESS = 'Установлено подразделение: ';
    const SET_BUSINESS_PROCESS = 'Установлен бизнес процесc: ';
    const SET_BUSINESS_PROCESS_STATUS = 'Установлен статус бизнес процесса: ';
    const SET_CLIENT_BLOCKED_TRUE = 'Лицевой счет заблокирован';
    const SET_CLIENT_BLOCKED_FALSE = 'Лицевой счет разблокирован';
    const SET_CLIENT_ACTIVE_TRUE = 'Лицевой счет открыт';
    const SET_CLIENT_ACTIVE_FALSE = 'Лицевой счет закрыт';

    public
        $contract_id,
        $user,
        $comment;

    public function rules()
    {
        return [
            [['contract_id',], 'integer'],
            [['contract_id', 'comment',], 'required'],
            [['user_id',], 'string'],
        ];
    }

    public function save()
    {
        $comment = new ClientContractComment;
        $comment->setAttributes($this->getAttributes(), false);

        $comment->user = $this->user ?: Yii::$app->user->identity->user;
        $comment->ts = (new DateTime('now'))->format('Y-m-d H:i:s');
        $comment->is_publish = 0;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $comment->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

}
