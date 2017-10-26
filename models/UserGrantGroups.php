<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use Yii;

class UserGrantGroups extends ActiveRecord
{
    const GROUP_MANAGER = 'manager';
    const GROUP_ACCOUNT_MANAGER = 'account_managers';
    const GROUP_ADMIN = 'admin';

    public static function tableName()
    {
        return 'user_grant_groups';
    }

    public static function setRights(UserGroups $group, array $rights)
    {
        $baseRights = UserRight::find()->all();

        foreach ($rights as $resource => $actions) {
            foreach ($baseRights as $baseRight) {
                if ($resource != $baseRight->resource) {
                    continue;
                }

                $groupRightsAccess = explode(',', $baseRight->values);
                $userRights = [];
                for ($i = 0, $s = sizeof($actions); $i < $s; $i++) {
                    if (in_array($actions[$i], $groupRightsAccess)) {
                        $userRights[] = $actions[$i];
                    }
                }

                $currentRights = self::findOne(['resource' => $resource, 'name' => $group->usergroup]);

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($currentRights instanceof self) {
                        $currentRights->delete();
                    }

                    $rights = new self;
                    $rights->resource = $resource;
                    $rights->name = $group->usergroup;
                    $rights->access = implode(',', $userRights);
                    $rights->save();

                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }
    }


}
