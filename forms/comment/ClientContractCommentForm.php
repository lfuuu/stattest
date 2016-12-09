<?php

namespace app\forms\comment;

use Yii;
use DateTime;
use DateTimeZone;
use app\classes\Form;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContractComment;
use app\models\User;

class ClientContractCommentForm extends Form
{
    const SET_BUSINESS = 'Установлено подразделение: ';
    const SET_BUSINESS_PROCESS = 'Установлен бизнес процесc: ';
    const SET_BUSINESS_PROCESS_STATUS = 'Установлен статус бизнес процесса: ';
    const SET_CLIENT_BLOCKED_TRUE = 'Лицевой счет заблокирован';
    const SET_CLIENT_BLOCKED_FALSE = 'Лицевой счет разблокирован';
    const SET_CLIENT_ACTIVE_TRUE = 'Лицевой счет открыт';
    const SET_CLIENT_ACTIVE_FALSE = 'Лицевой счет закрыт';
    const SET_CLIENT_VOIP_DISABLED_TRUE = 'Локальная блокировка включена';
    const SET_CLIENT_VOIP_DISABLED_FALSE = 'Локальная блокировка выключена';

    public
        $contract_id,
        $user,
        $comment;

    public function rules()
    {
        return [
            [['contract_id',], 'integer'],
            [['contract_id', 'comment',], 'required'],
            [['user',], 'string'],
        ];
    }

    public function save()
    {
        $comment = new ClientContractComment;
        $comment->setAttributes($this->getAttributes(), false);

        if ($this->user) {
            $comment->user = $this->user;
        } elseif (Yii::$app->user->identity && Yii::$app->user->identity->user) {
            $comment->user = Yii::$app->user->identity->user;
        } else {
            /** @var User $user */
            $user = User::findOne(['id' => User::SYSTEM_USER_ID]);
            $comment->user = $user ? $user->user : '';
        }
        $comment->ts = (new DateTime('now', new DateTimeZone('UTC')))->format(DateTimeZoneHelper::DATETIME_FORMAT);
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
