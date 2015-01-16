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
            if (!$user) return false;

            $permissions = [];

            $groupGrunts = UserGrantGroups::find()->where(['name' => $user->usergroup])->asArray()->all();
            foreach ($groupGrunts as $grunt) {
                foreach (explode(',', $grunt['access']) as $permission) {
                    $permissions[$grunt['resource'] . '.' . $permission] = true;
                }
            }

            $userGrunts = UserGrantUsers::find()->where(['name' => $user->user])->asArray()->all();
            foreach ($userGrunts as $grunt) {
                foreach (explode(',', $grunt['access']) as $permission) {
                    $permissions[$grunt['resource'] . '.' . $permission] = true;
                }
            }

            $this->permissionsByUser[$userId] = $permissions;
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
}
