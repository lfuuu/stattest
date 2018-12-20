<?php

namespace app\models;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;


/**
 * Class ClientBlockedComment
 *
 * @property int $id
 * @property int $account_id
 * @property string $comment
 * @property string $created_at
 *
 * @property-read ClientAccount $client
 *
 * @method static ClientBlockedComment findOne($condition)
 */
class ClientBlockedComment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName()
    {
        return 'client_blocked_comment';
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['account_id', 'required'],
            ['account_id', 'integer'],
            ['comment', 'string'],
        ];
    }
}