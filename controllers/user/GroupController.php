<?php

namespace app\controllers\user;


use Yii;
use yii\helpers\Json;
use app\classes\Assert;
use app\classes\BaseController;
use yii\filters\AccessControl;
use app\models\UserGroups;
use app\forms\user\GroupForm;


class GroupController extends BaseController
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
                        'actions' => ['add', 'edit', 'delete'],
                        'roles' => ['users.change'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new GroupForm;
        $model->load(Yii::$app->request->getQueryParams());

        $dataProvider = $model->spawnDataProvider();
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdd()
    {
        $model = new GroupForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            Yii::$app->session->set(
                'group_created',
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
        $group = UserGroups::findOne($id);
        Assert::isObject($group);

        $model = (new GroupForm)->initModel($group);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save($group)) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены');
            return $this->redirect(Yii::$app->request->referrer);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $group = UserGroups::findOne($id);
        Assert::isObject($group);

        (new GroupForm)->delete($group);

        return $this->redirect(['index']);
    }

}