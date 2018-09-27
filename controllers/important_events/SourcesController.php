<?php

namespace app\controllers\important_events;

use app\classes\BaseController;
use app\models\important_events\ImportantEventsSources;
use Yii;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
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
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['dictionary.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['dictionary-important-event.important-events-sources'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
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

    /**
     * @param string $id
     * @return string
     * @throws InvalidParamException
     */
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

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $model = ImportantEventsSources::findOne($id);

        if ($model instanceof ImportantEventsSources) {
            $model->delete();
        }

        return $this->redirect('/important_events/sources');
    }

}