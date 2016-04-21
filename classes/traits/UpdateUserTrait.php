<?php
namespace app\classes\traits;

use app\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * Методы для полей update_time, update_user_id
 *
 * @property string $update_time
 * @property integer $update_user_id
 * @property User $updateUser
 *
 * @method hasOne($class, array $link) ActiveQuery
 *
 * migration
 * 'update_time' => $this->dateTime(),
 * 'update_user_id' => $this->integer(),
 *
 * $fieldName = 'update_user_id';
 * $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, User::tableName(), 'id', 'SET NULL');
 *
 *
 * messages
 * 'update_time' => 'Когда редактировал',
 * 'update_user_id' => 'Кто редактировал',
 *
 *
 * model
 * // Методы для полей update_time, update_user_id
 * use \app\classes\traits\UpdateUserTrait;
 */
trait UpdateUserTrait
{
    /**
     * @return ActiveQuery
     */
    public function getUpdateUser()
    {
        return $this->hasOne(User::className(), ['id' => 'update_user_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert, $isCallParent = true)
    {
//        $this->update_time = date('c'); // new CDbExpression('NOW()')
        $this->update_user_id = Yii::$app->user->getId();

        if ($isCallParent) {
            return parent::beforeSave($insert);
        } else {
            return true;
        }
    }
}