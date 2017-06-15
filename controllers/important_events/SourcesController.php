<?php

namespace app\controllers\important_events;

use Yii;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use app\models\important_events\ImportantEventsSources;
use yii\filters\AccessControl;

class SourcesController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['dictionary.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['dictionary.important-events-sources'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ImportantEventsSources::find(),
        ]);
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionEdit($id = '')
    {
        $model = ImportantEventsSources::findOne($id);
        if (!($model instanceof ImportantEventsSources)) {
            $model = new ImportantEventsSources;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/important_events/sources');
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = ImportantEventsSources::findOne($id);

        if ($model instanceof ImportantEventsSources) {
            $model->delete();
        }

        return $this->redirect('/important_events/sources');
    }

}