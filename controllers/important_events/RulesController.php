<?php

namespace app\controllers\important_events;

use Yii;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use app\models\important_events\ImportantEventsRules;

class RulesController extends BaseController
{

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ImportantEventsRules::find(),
        ]);
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionEdit($id = 0)
    {
        $model = ImportantEventsRules::findOne($id);
        if (!($model instanceof ImportantEventsRules)) {
            $model = new ImportantEventsRules;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/important_events/rules');
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    public function actionDelete($rule_id)
    {
        $model = ImportantEventsRules::findOne($rule_id);

        if ($model instanceof ImportantEventsRules) {
            $model->delete();
        }

        return $this->redirect('/important_events/rules');
    }

}