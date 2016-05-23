<?php

namespace app\controllers\important_events;

use Yii;
use app\classes\BaseController;
use app\models\important_events\ImportantEventsNames;

class NamesController extends BaseController
{

    public function actionIndex()
    {
        $searchModel = new ImportantEventsNames;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
        ]);
    }

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

    public function actionDelete($id)
    {
        $model = ImportantEventsNames::findOne($id);

        if ($model instanceof ImportantEventsNames) {
            $model->delete();
        }

        return $this->redirect('/important_events/names');
    }

}