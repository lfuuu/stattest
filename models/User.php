<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\dao\user\UserDao;
use app\queries\UserQuery;
use Yii;
use yii\base\NotSupportedException;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @property integer $id
 * @property string $user
 * @property string $pass
 * @property string $usergroup
 * @property string $name
 * @property string $email
 * @property string $phone_work
 * @property string $incoming_phone
 * @property string $data_flag
 * @property integer $depart_id
 * @property string $enabled
 *
 * @method static User findOne($condition)
 */
class User extends ActiveRecord implements \yii\web\IdentityInterface
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const SYSTEM_USER = 'system';
    const SYSTEM_USER_ID = 60;
    const CLIENT_USER_ID = 25;
    const LK_USER_ID = 177;
    const DEFAULT_ACCOUNT_MANAGER_USER_ID = 10; // Владимир Ан
    const DEFAULT_ACCOUNT_MANAGER_USER = 'ava'; // Владимир Ан

    const DEPART_SALES = 28;
    const DEPART_PURCHASE = 29;

    const PHOTO_SIZE_OF_SQUARE_SIDE = 250;

    const DEFAULT_INCOMING_PHONE = '+7 (495) 105-99-99';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'user_users';
    }

    /**
     * @return UserDao
     */
    public static function dao()
    {
        return UserDao::me();
    }

    /**
     * @return UserQuery
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @param int $id
     * @return User
     */
    public static function findIdentity($id)
    {
        return $id == static::SYSTEM_USER_ID ? null : static::findOne(['id' => $id, 'enabled' => 'yes']);
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return User|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if ($token == Yii::$app->params['API_SECURE_KEY']) {
            return self::findOne(['id' => Yii::$app->user->getId() ?: static::SYSTEM_USER_ID]);
        }

        return null;
    }

    /**
     * @param string $user
     * @return User
     */
    public static function findByUsername($user)
    {
        $user = static::findOne(['user' => $user]);

        if ($user && $user->enabled != 'yes') {
            $user->name = "(--" . $user->name . "--)";
        }

        return $user;
    }

    /**
     * @param string $token
     * @throws NotSupportedException
     */
    public static function findByPasswordResetToken($token)
    {
        throw new NotSupportedException('"findByPasswordResetToken" is not implemented.');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }


    /**
     * @return bool
     */
    public function getAuthKey()
    {
        return false;
    }

    /**
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return false; // $this->getAuthKey() === $authKey;
    }

    /**
     * @param string $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return $this->pass === md5($password);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupRights()
    {
        return $this->hasMany(UserGrantGroups::className(), ['name' => 'usergroup']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRights()
    {
        return $this->hasMany(UserGrantUsers::className(), ['name' => 'user']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(UserGroups::className(), ['usergroup' => 'usergroup']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDepartment()
    {
        return $this->hasOne(UserDeparts::className(), ['id' => 'depart_id']);
    }

    /**
     * @return array
     */
    public static function getAccountManagerList()
    {
        return self::getListTrait(
            $isWithEmpty = false,
            $isWithNullAndNotNull = false,
            $indexBy = 'user',
            $select = 'name',
            $orderBy = [],
            $where = ['enabled' => 'yes', 'depart_id' => self::DEPART_SALES]
        );
    }

    /**
     * @return array
     */
    public static function getManagerList()
    {
        return self::getListTrait(
            $isWithEmpty = false,
            $isWithNullAndNotNull = false,
            $indexBy = 'user',
            $select = 'name',
            $orderBy = [],
            $where = ['enabled' => 'yes', 'depart_id' => self::DEPART_SALES]
        );
    }

    /**
     * @param int $departmentId - Department ID
     * @param array $options - Extends options
     *                       - "primary": primary column for array
     *                       - "enabled": extends selection on column "enabled = yes"
     * @return array
     */
    public static function getUserListByDepart($departmentId, $options = [])
    {
        return self::getListTrait(
            $isWithEmpty = false,
            $isWithNullAndNotNull = false,
            $indexBy = isset($options['primary']) ? $options['primary'] : 'id',
            $select = 'name',
            $orderBy = [],
            $where = [
                'AND',
                ['depart_id' => $departmentId],
                isset($options['enabled']) ? ['enabled' => 'yes'] : []
            ]
        );
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param string $indexBy
     * @return \string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $indexBy = 'user'
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy,
            $select = 'CONCAT(name, " (", user, ")")',
            $orderBy = ['name' => SORT_ASC],
            $where = ['enabled' => 'yes']
        );
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return Url::to(['/', 'module' => 'employeers', 'user' => $this->user]);
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return Html::a($this->name, $this->getUrl());
    }
}
