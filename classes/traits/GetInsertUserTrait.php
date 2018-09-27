<?php
namespace app\classes\traits;

use app\helpers\DateTimeZoneHelper;
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
trait GetInsertUserTrait
{
    /**
     * @return ActiveQuery
     */
    public function getInsertUser()
    {
        return $this->hasOne(User::class, ['id' => 'insert_user_id']);
    }
}