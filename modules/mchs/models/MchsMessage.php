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
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date',
                'updatedAtAttribute' => null,
                'value' => new Expression("NOW()"),
            ],
            [
                // Установить "кто создал"
                'class' => AttributeBehavior::className(),
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
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Отправка сообщения
     *
     * @return bool
     */
    public function send()
    {
        $phones = $this->_getMvnoActivePhoneList();

        try {
            ApiMvnoConnector::me()->send($phones, $this->message);
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

    /**
     * Список телефонов
     *
     * @return string[]
     */
    public function _getMvnoActivePhoneList()
    {
        return Number::find()
            ->select('number')
            ->where([
                'status' => Number::$statusGroup[Number::STATUS_GROUP_ACTIVE],
                'ndc_type_id' => NdcType::ID_MOBILE,
            ])
            ->andWhere(['IS NOT', 'mvno_trunk_id', null])
            ->andWhere(['>', 'mvno_trunk_id', 0])
            ->column();
    }

}
