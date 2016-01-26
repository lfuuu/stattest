<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class UserGrantUsers extends ActiveRecord
{

    public static function tableName()
    {
        return 'user_grant_users';
    }

    public static function setRights(User $user, array $rights)
    {
        $baseRights = UserRight::find()->all();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            self::deleteAll(['name' => $user->user]);
            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        if (!count($rights)) {
            return true;
        }

        foreach ($rights as $resource => $actions) {
            foreach ($baseRights as $baseRight) {
                if ($resource != $baseRight->resource) {
                    continue;
                }

                $groupRightsAccess = explode(',', $baseRight->values);
                $userRights = [];
                foreach ($actions as $action) {
                    if (in_array($action, $groupRightsAccess, true)) {
                        $userRights[] = $action;
                    }
                }

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $rights = new self;
                    $rights->resource = $resource;
                    $rights->name = $user->user;
                    $rights->access = implode(',', $userRights);
                    $rights->save();

                    $transaction->commit();

                    continue(2);
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }
    }

}
