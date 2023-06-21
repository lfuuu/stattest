<?php

namespace app\classes\contragent\importer\lk;

use app\models\User;

class ContragentLkImporter
{
    public function run($contragentId = null)
    {
        $identity = \Yii::$app->user->identity;
        \Yii::$app->user->setIdentity(User::findOne(['id' => User::LK_USER_ID]));

        /** @var CoreLkContragent $obj */
        foreach (DataLoader::getObjectsForSync($contragentId) as $obj) {
            $obj
                ->getTransformatorByType()
                ->update();
        }

        \Yii::$app->user->setIdentity($identity);
    }
}

