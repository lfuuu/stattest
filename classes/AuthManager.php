<?php
namespace app\classes;

use app\models\User;
use app\models\UserGrantGroups;
use app\models\UserGrantUsers;
use app\models\UserRight;
use Yii;
use yii\base\NotSupportedException;
use yii\rbac\BaseManager;
use yii\rbac\Item;
use yii\rbac\Role;


class AuthManager extends BaseManager
{
    protected $permissionsByUser = [];

    /**
     * @inheritdoc
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        $permissions = $this->getPermissionsByUser($userId);
        return isset($permissions[$permissionName]);
    }

    /**
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function addChild($parent, $child)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function removeChild($parent, $child)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function hasChild($parent, $child)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function assign($role, $userId)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function revoke($role, $userId)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function revokeAll($userId)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function getAssignment($roleName, $userId)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function getItems($type)
    {
        throw new NotSupportedException();
    }


    /**
     * @inheritdoc
     */
    public function removeItem($item)
    {
        throw new NotSupportedException();
    }

    /**
     * Removed all children form their parent.
     * Note, the children items are not deleted. Only the parent-child relationships are removed.
     * @param Item $parent
     * @return boolean whether the removal is successful
     */
    public function removeChildren($parent)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function getItem($name)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function updateRule($name, $rule)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function getRule($name)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function getRolesByUser($userId)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function getPermissionsByRole($roleName)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function getPermissionsByUser($userId)
    {
        if (!isset($this->permissionsByUser[$userId])) {

            $user = User::findOne($userId);
            if (!$user) {
                return false;
            }

            $permissions = [];

            $groupGrunts = UserGrantGroups::find()->where(['name' => $user->usergroup])->asArray()->all();
            foreach ($groupGrunts as $grunt) {
                $permissions[$grunt['resource']] = explode(',', $grunt['access']);
            }

            $userGrunts = UserGrantUsers::find()->where(['name' => $user->user])->asArray()->all();
            foreach ($userGrunts as $grunt) {
                foreach (explode(',', $grunt['access']) as $permission) {
                    $permissions[$grunt['resource']] = explode(',', $grunt['access']);
                }
            }

            $_permissions = [];
            foreach($permissions as $section => $sectionPermissions) {
                foreach ($sectionPermissions as $sectionPermission) {
                    $_permissions[$section . '.' . $sectionPermission] = true;
                }
            }

            $this->permissionsByUser[$userId] = $_permissions;
        }

        return $this->permissionsByUser[$userId];
    }

    /**
     * @inheritdoc
     */
    public function getChildren($name)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function removeAllPermissions()
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function removeAllRoles()
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function removeAllRules()
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    public function removeAllAssignments()
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    protected function removeRule($rule)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    protected function addRule($rule)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    protected function updateItem($name, $item)
    {
        throw new NotSupportedException();
    }

    /**
     * @inheritdoc
     */
    protected function addItem($item)
    {
        throw new NotSupportedException();
    }

    public function updateDatabase()
    {
        $rightsConfig = Yii::$app->params['rights'];

        /** @var UserRight[] $userRights */
        $userRights = UserRight::find()->all();
        $exists = [];

        // Обновить существующие права
        foreach ($userRights as $userRight) {
            if (isset($rightsConfig[$userRight->resource])) {
                $exists[$userRight->resource] = true;

                $right = $rightsConfig[$userRight->resource];
                $userRight->comment = $right['name'];
                $userRight->values = implode(',', array_keys($right['permissions']));
                $userRight->values_desc = implode(',', array_values($right['permissions']));
                $userRight->save();
            } else {
                $userRight->delete();
            }
        }

        // Добавить новые права
        foreach ($rightsConfig as $resource => $right) {
            if (!isset($exists[$resource])) {
                $userRight = new UserRight();
                $userRight->resource = $resource;
                $userRight->comment = $right['name'];
                $userRight->values = implode(',', array_keys($right['permissions']));
                $userRight->values_desc = implode(',', array_values($right['permissions']));
                $userRight->save();
            }
        }
    }

    public static function getPasswordHash($password)
    {
        return md5($password);
    }

    /**
     * @todo Необходимо реализовать этот метод, иначе класс будет абстрактным. Пока сделал заглушку
     * @param string $roleName
     * @return array
     */
    public function getUserIdsByRole($roleName)
    {
        return [];
    }

    /**
     * Checks the possibility of adding a child to parent
     * @param Item $parent the parent item
     * @param Item $child the child item to be added to the hierarchy
     * @return boolean possibility of adding
     *
     * @since 2.0.8
     */
    public function canAddChild($parent, $child)
    {
        // TODO: Implement canAddChild() method.
        return false;
    }

    /**
     * Returns child roles of the role specified. Depth isn't limited.
     * @param string $roleName name of the role to file child roles for
     * @return Role[] Child roles. The array is indexed by the role names.
     * First element is an instance of the parent Role itself.
     * @throws \yii\base\InvalidParamException if Role was not found that are getting by $roleName
     * @since 2.0.10
     */
    public function getChildRoles($roleName)
    {
        // TODO: Implement getChildRoles() method.
    }
}
