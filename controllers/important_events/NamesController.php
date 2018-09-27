<?php

namespace app\controllers\important_events;

use app\classes\BaseController;
use app\models\important_events\ImportantEventsNames;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;

class NamesController extends BaseController
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
                        'roles' => ['dictionary-important-event.important-events-names'],
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
        $searchModel = new ImportantEventsNames;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
        ]);
    }

    /**
     * @param string $id
     * @return string
     * @throws InvalidParamException
     */
    public function actionEdit($id = '')
    {
        $eventCode = Yii::$app->request->get('code');

        $model = ImportantEventsNames::findOne($id);
        if (!($model instanceof ImportantEventsNames)) {
            $model = new ImportantEventsNames;
            if (!empty($eventCode)) {
                $model->code = $eventCode;
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/important_events/names');
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
        $model = ImportantEventsNames::findOne($id);

        if ($model instanceof ImportantEventsNames) {
            $model->delete();
        }

        return $this->redirect('/important_events/names');
    }

}