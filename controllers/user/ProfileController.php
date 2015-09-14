<?php

namespace app\controllers\user;

use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;
use yii\helpers\Json;
use app\forms\user\UserProfileForm;
use app\forms\user\UserPasswordForm;

class ProfileController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['usercontrol.edit_full'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['change-password'],
                        'roles' => ['usercontrol.edit_pass'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $model = (new UserProfileForm)->initModel($user);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($user)) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            return $this->redirect(Yii::$app->request->referrer);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionChangePassword()
    {
        $model = new UserPasswordForm;
        $model->scenario = 'profile';

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save(Yii::$app->user->identity)) {
            Yii::$app->session->setFlash('success', true);
            Yii::$app->session->set(
                'user_data',
                Json::encode(Yii::$app->user->identity)
            );
            return $this->redirect(Yii::$app->request->referrer);
        }

        $this->layout = 'minimal';
        return $this->render('//user/passwd_edit', [
            'model' => $model,
        ]);
    }

}