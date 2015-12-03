<?php

namespace app\controllers\important_events;

use Yii;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use app\models\important_events\ImportantEventsSources;

class SourcesController extends BaseController
{

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