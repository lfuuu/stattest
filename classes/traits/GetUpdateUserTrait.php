<?php
namespace app\classes\traits;

use app\helpers\DateTimeZoneHelper;
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
trait GetUpdateUserTrait
{
    /**
     * @return ActiveQuery
     */
    public function getUpdateUser()
    {
        return $this->hasOne(User::class, ['id' => 'update_user_id']);
    }
}