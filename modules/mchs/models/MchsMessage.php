<?php

namespace app\modules\mchs\models;

use app\classes\model\ActiveRecord;
use app\models\Number;
use app\models\User;
use app\modules\mchs\classes\api\ApiMvnoConnector;
use app\modules\nnp\models\NdcType;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Class MessageMchs
 *
 * @property integer $id
 * @property string $message
 * @property integer $user_id
 * @property string $date
 * @property string $status
 * @property-read User $user
 */
class MchsMessage extends ActiveRecord
{
    private $_error = null;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'mchs_message';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал"
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'date',
                'updatedAtAttribute' => null,
                'value' => new Expression("NOW()"),
            ],
            [
                // Установить "кто создал"
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['user_id'],
                ],
                'value' => \Yii::$app->user->getId(),
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => "№",
            'message' => 'Текст сообщения',
            'date' => 'Дата создания и отправки',
            'user_id' => 'Пользователь',
            'status' => 'Статус',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Отправка сообщения
     *
     * @return bool
     */
    public function send()
    {
        try {
            ApiMvnoConnector::me()->send($this->message);
        } catch (\Exception $e) {
            $this->_error = 'Error: ' . $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Получение текста ошибки
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

}
