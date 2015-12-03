<?php

namespace app\controllers\important_events;

use Yii;
use app\classes\BaseController;
use yii\data\ActiveDataProvider;
use app\models\important_events\ImportantEventsGroups;

class GroupsController extends BaseController
{

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ImportantEventsGroups::find(),
        ]);
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionEdit($id = 0)
    {
        $model = ImportantEventsGroups::findOne($id);
        if (!($model instanceof ImportantEventsGroups)) {
            $model = new ImportantEventsGroups;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/important_events/groups');
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }

    public function actionDelete($group_id)
    {
        $model = ImportantEventsGroups::findOne($group_id);

        if ($model instanceof ImportantEventsGroups) {
            $model->delete();
        }

        return $this->redirect('/important_events/groups');
    }

}