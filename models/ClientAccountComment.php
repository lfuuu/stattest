<?php

namespace app\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\FormFieldValidator;
use app\exceptions\ModelValidationException;

/**
 * Class ClientAccountComment
 *
 * @property int $id
 * @property int $account_id
 * @property string $comment
 * @property int $user_id
 * @property string $created_at
 * @property-read User $user
 */
class ClientAccountComment extends ActiveRecord
{

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_comment';
    }

    public function rules()
    {
        return [
            [['comment', 'account_id'], 'required'],
            ['user_id', 'integer'],
            ['user_id', 'default', 'value' => User::SYSTEM_USER_ID],
            ['account_id', AccountIdValidator::className()],
            ['comment', FormFieldValidator::className()],
            ['comment', 'string', 'min' => 3],
        ];
    }

    public function behaviors()
    {
        return [
            CreatedAt::className()
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Добавление коментария к ЛС
     *
     * @param integer $accountId
     * @param string $commentStr
     * @param integer $userId
     * @throws ModelValidationException
     */
    public static function addComment($accountId, $commentStr, $userId = null)
    {
        $comment = new self;
        $comment->account_id = $accountId;
        $comment->comment = $commentStr;

        if (!$userId && \Yii::$app->user) {
            $userId = \Yii::$app->user->id;
        }

        $comment->user_id = $userId;

        if (!$comment->save()) {
            throw new ModelValidationException($comment);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->comment;
    }

}
