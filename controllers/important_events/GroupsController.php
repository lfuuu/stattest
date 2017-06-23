<?php

namespace app\controllers\important_events;

use app\classes\BaseController;
use app\models\important_events\ImportantEventsGroups;
use Yii;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class GroupsController extends BaseController
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
                        'roles' => ['dictionary-important-event.important-events-groups'],
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
            'query' => ImportantEventsGroups::find(),
        ]);
        $dataProvider->sort = false;

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws InvalidParamException
     */
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

    /**
     * @param $group_id
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionDelete($group_id)
    {
        $model = ImportantEventsGroups::findOne($group_id);

        if ($model instanceof ImportantEventsGroups) {
            $model->delete();
        }

        return $this->redirect('/important_events/groups');
    }

}