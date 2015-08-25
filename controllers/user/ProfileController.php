<?php

namespace app\controllers\user;

use Yii;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\forms\user\UserProfileForm;

class ProfileController extends BaseController
{

    public function behaviors()
    {
        /*
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['clients.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['clients.new'],
                    ],
                ],
            ],
        ];
        */
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

}