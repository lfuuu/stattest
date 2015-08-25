<?php

namespace app\controllers\user;

use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;
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
        $model = (new UserProfileForm)->initModel();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save(Yii::$app->user->identity)) {
            $this->redirect('/user/profile');
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionChangePassword()
    {
        $model = new UserPasswordForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/user/profile/change-password');
        }

        $this->layout = 'minimal';
        return $this->render('passwd_edit', [
            'model' => $model,
        ]);
    }

}