<?php

namespace app\controllers\important_events;

use Yii;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use app\models\important_events\ImportantEventsNames;

class NamesController extends BaseController
{

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ImportantEventsNames::find(),
        ]);
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionEdit($id = '')
    {
        $model = ImportantEventsNames::findOne($id);
        if (!($model instanceof ImportantEventsNames)) {
            $model = new ImportantEventsNames;
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