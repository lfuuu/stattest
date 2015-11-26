<?php

namespace app\controllers;

use Yii;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use app\models\ImportantEvents;
use app\models\ImportantEventsRules;

class ImportantEventsController extends BaseController
{

    public function actionIndex()
    {
        $searchModel = new ImportantEvents;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('report', [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
        ]);
    }

    public function actionRules()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ImportantEventsRules::find(),
        ]);
        $dataProvider->sort = false;

        return $this->render('rules/grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionRulesEdit($id = 0)
    {
        $model = ImportantEventsRules::findOne($id);
        if (!($model instanceof ImportantEventsRules)) {
            $model = new ImportantEventsRules;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/important-events/rules');
        }

        return $this->render('rules/form', [
            'model' => $model,
        ]);
    }

    public function actionRulesDelete($rule_id)
    {
        $model = ImportantEventsRules::findOne($rule_id);

        if ($model instanceof ImportantEventsRules) {
            $model->delete();
        }

        return $this->redirect('/important-events/rules');
    }

}