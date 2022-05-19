<?php

namespace app\controllers\user;

use app\classes\Form;
use Yii;
use yii\data\ActiveDataProvider;
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
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
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

    /**
     * Controller /user/group
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new GroupForm;
        $model->load(Yii::$app->request->getQueryParams());

        $query = $model->spawnQuery();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Form::PAGE_SIZE,
            ],
        ]);

        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Добавление группы
     *
     * @return string|\yii\web\Response
     */
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

    /**
     * Редактирование группы
     *
     * @param integer $id
     * @return string|\yii\web\Response
     */
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

    /**
     * Удаление группы
     *
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $group = UserGroups::findOne($id);
        Assert::isObject($group);

        (new GroupForm)->delete($group);

        return $this->redirect(['index']);
    }

}