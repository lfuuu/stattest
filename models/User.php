<?php
namespace app\models;

use app\queries\UserQuery;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\dao\user\UserDao;

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
    const SYSTEM_USER_ID = 60;
    const CLIENT_USER_ID = 25;
    const LK_USER_ID = 177;
    const DEFAULT_ACCOUNT_MANAGER_USER_ID = 10;//Владимир Ан

    const DEPART_SALES = 28;
    const DEPART_PURCHASE = 29;

    const PHOTO_SIZE_OF_SQUARE_SIDE = 250;

    public static function tableName()
    {
        return 'user_users';
    }

    public static function dao()
    {
        return UserDao::me();
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
        $user = static::findOne(['user' => $user]);

        if ($user)
            if ($user->enabled != 'yes')
                $user->name = "(--".$user->name."--)";

        return $user;
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

    public function getGroupRights()
    {
        return $this->hasMany(UserGrantGroups::className(), ['name' => 'usergroup']);
    }

    public function getUserRights()
    {
        return $this->hasMany(UserGrantUsers::className(), ['name' => 'user']);
    }

    public function getGroup()
    {
        return $this->hasOne(UserGroups::className(), ['usergroup' => 'usergroup']);
    }

    public function getDepartment()
    {
        return $this->hasOne(UserDeparts::className(), ['id' => 'depart_id']);
    }

    public static function getAccountManagerList()
    {
        $arr = self::find()
            //->andWhere(['in', 'usergroup', ['account_managers', 'manager']])
            ->andWhere(['depart_id' => self::DEPART_SALES])
            ->andWhere(['enabled' => 'yes'])
            ->all();
        return ArrayHelper::map($arr, 'user', 'name', 'depart_id');
    }

    public static function getManagerList()
    {
        $arr = self::find()
            //->andWhere(['in', 'usergroup', ['account_managers', 'manager']])
            ->andWhere(['depart_id' => self::DEPART_SALES])
            ->andWhere(['enabled' => 'yes'])
            ->all();
        return ArrayHelper::map($arr, 'user', 'name');
    }

    /**
     * @param $id - Department ID
     * @param array $options - Extends options
     *                       - "primary": primary column for array
     *                       - "enabled": extends selection on column "enabled = yes"
     * @return array
     */
    public static function getUserListByDepart($id, $options = [])
    {
        $models = self::find()->andWhere(['depart_id' => $id]);

        if (isset($options['enabled']))
            $models->andWhere(['enabled' => 'yes']);

        $primary = isset($options['primary']) ? $options['primary'] : 'id';

        return ArrayHelper::map($models->all(), $primary, 'name');
    }
}
