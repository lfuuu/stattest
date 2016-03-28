<?php
namespace app\classes\traits;

use app\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * Методы для полей insert_time, insert_user_id
 *
 * @property string $insert_time
 * @property integer $insert_user_id
 * @property User $insertUser
 *
 * @method hasOne($class, array $link) ActiveQuery
 *
 *
 * migration
 * 'insert_time' => $this->timestamp()->notNull(), // dateTime
 * 'insert_user_id' => $this->integer(),
 *
 * $fieldName = 'insert_user_id';
 * $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, User::tableName(), 'id', 'SET NULL');
 *
 *
 * messages
 * 'insert_time' => 'Когда создал',
 * 'insert_user_id' => 'Кто создал',
 *
 *
 * model
 * // Методы для полей insert_time, insert_user_id
 * use \app\classes\traits\InsertUserTrait;
 */
trait InsertUserTrait
{
    /**
     * @return ActiveQuery
     */
    public function getInsertUser()
    {
        return $this->hasOne(User::className(), ['id' => 'insert_user_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->insert_time = date('c'); 
            $this->insert_user_id = Yii::$app->user->getId();
        }

        return parent::beforeSave($insert);
    }
}