<?php
namespace app\controllers\tariffication;

use app\forms\tariffication\ServiceAddForm;
use app\forms\tariffication\ServiceEditForm;
use app\forms\tariffication\ServiceListForm;
use app\models\tariffication\Service;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\classes\BaseController;
use yii\web\BadRequestHttpException;

class ServiceController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }


    public function actionIndex()
    {
        $model = new ServiceListForm();
        $model->load(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $model->spawnDataProvider(),
            'filterModel' => $model,
        ]);
    }

    public function actionAdd()
    {
        $model = new ServiceAddForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('index');
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionEdit($id)
    {
        $model = new ServiceEditForm();

        if (Yii::$app->request->isGet) {
            $feature = Service::findOne($id);
            if ($feature === null) throw new BadRequestHttpException();
            $model->setAttributes($feature->getAttributes(), false);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('index');
        } else {

        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }
}
