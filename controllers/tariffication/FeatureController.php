<?php
namespace app\controllers\tariffication;

use app\forms\tariffication\FeatureAddForm;
use app\forms\tariffication\FeatureEditForm;
use app\forms\tariffication\FeatureListForm;
use app\models\tariffication\Feature;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use app\classes\BaseController;
use yii\web\BadRequestHttpException;

class FeatureController extends BaseController
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
        $model = new FeatureListForm();
        $model->load(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $model->spawnDataProvider(),
            'filterModel' => $model,
        ]);
    }

    public function actionAdd()
    {
        $model = new FeatureAddForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('index');
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionEdit($id)
    {
        $model = new FeatureEditForm();

        if (Yii::$app->request->isGet) {
            $feature = Feature::findOne($id);
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
