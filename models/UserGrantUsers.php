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
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        foreach ($rights as $resource => $actions) {
            foreach ($baseRights as $baseRight) {
                if ($resource != $baseRight->resource)
                    continue;

                $groupRightsAccess = explode(',', $baseRight->values);
                $userRights = [];
                for ($i=0, $s=sizeof($actions); $i<$s; $i++) {
                    if (in_array($actions[$i], $groupRightsAccess))
                        $userRights[] = $actions[$i];
                }

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $rights = new self;
                    $rights->resource = $resource;
                    $rights->name = $user->user;
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
