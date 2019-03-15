<?php
namespace app\controllers;

use app\classes\BaseController;
use app\dao\TroubleDao;
use app\models\Trouble;
use app\models\User;
use Yii;

class TroubleController extends BaseController
{
    public function actionAddStage()
    {
        $get = Yii::$app->request->get();
        if (!$get['trouble_ids'] || !$get['user'] || !$get['state']) {
            Yii::$app->session->setFlash('error', 'Необходимо выбрать пользователя, состояние и траблы');
            return $this->redirect(Yii::$app->request->referrer);
        }

        $troublesQuery = Trouble::find()->where(['id' => $get['trouble_ids']]);
        $userId = User::find()->select('id')->where(['user' => $get['user']])->scalar();

        $state = $get['state'];

        foreach ($troublesQuery->each() as $trouble) {
            TroubleDao::me()->addStage($trouble, $state, '', null, $userId);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}
