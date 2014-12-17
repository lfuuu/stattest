<?php
namespace app\models;

use app\queries\UserQuery;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $user
 * @property string $pass
 * @property string $usergroup
 * @property string $name
 * @property string $data_flag
 * @property integer $depart_id
 * @property string $enabled
 */
class User extends ActiveRecord implements \yii\web\IdentityInterface
{
    public static function tableName()
    {
        return 'user_users';
    }

    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'enabled' => 'yes']);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        if ($token == Yii::$app->params['API_SECURE_KEY']) {
            return new User();
        }
        return null;
    }

    public static function findByUsername($user)
    {
        return static::findOne(['user' => $user, 'enabled' => 'yes']);
    }

    public static function findByPasswordResetToken($token)
    {
        throw new NotSupportedException('"findByPasswordResetToken" is not implemented.');
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }


    public function getAuthKey()
    {
        return false;
    }

    public function validateAuthKey($authKey)
    {
        return false; //$this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return $this->pass === md5($password);
    }
}
