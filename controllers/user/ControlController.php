<?php

namespace app\controllers\user;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use yii\web\Response;
use yii\filters\AccessControl;
use app\models\User;
use app\forms\user\UserForm;
use app\forms\user\UserListForm;
use app\forms\user\UserCreateForm;
use app\forms\user\UserPasswordForm;
use yii\helpers\Json;

class ControlController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['users.r'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add', 'edit', 'delete', 'change-password'],
                        'roles' => ['users.change'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update-rights'],
                        'roles' => ['users.grant'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new UserListForm;
        $model->load(Yii::$app->request->getQueryParams());

        $dataProvider = $model->spawnDataProvider();
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $model,
        ]);
    }

    public function actionAdd()
    {
        $model = new UserCreateForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            Yii::$app->session->set(
                'user_created',
                Json::encode($model)
            );
            return $this->redirect(Yii::$app->request->referrer);
        }

        $this->layout = 'minimal';
        return $this->render('add', [
            'model' => $model,
        ]);
    }

    public function actionEdit($id)
    {
        $user = User::findOne($id);
        Assert::isObject($user);

        $model = (new UserForm)->initModel($user);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($user)) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            return $this->redirect(Yii::$app->request->referrer);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $user = User::findOne($id);
        Assert::isObject($user);

        (new UserForm)->delete($user);

        return $this->redirect(['index']);
    }

    public function actionChangePassword($id)
    {
        $user = User::findOne($id);
        Assert::isObject($user);

        $model = new UserPasswordForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($user)) {
            Yii::$app->session->setFlash('success', true);
            return $this->redirect(Yii::$app->request->referrer);
        }

        $this->layout = 'minimal';
        return $this->render('//user/passwd_edit', [
            'model' => $model,
        ]);
    }

    public function actionAjaxDeptUsers($id)
    {
        if (!Yii::$app->request->isAjax)
            return;

        Yii::$app->response->format = Response::FORMAT_JSON;
        $usersList = User::getUserListByDepart($id, ['enabled' => true, 'primary' => 'user']);
        $output = [];

        foreach ($usersList as $user => $name) {
            $output[] = [
                'id' => $user,
                'text' => $name,
            ];
        }

        return $output;
    }

    public function actionUpdateRights()
    {
        $authManager = new \app\classes\AuthManager();
        $authManager->updateDatabase();
        Yii::$app->session->addFlash('success', 'Права обновлены');
    }

}