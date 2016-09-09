<?php

namespace app\controllers\important_events;

use Yii;
use yii\web\Response;
use ActiveRecord\RecordNotFound;
use app\exceptions\FormValidationException;
use app\classes\DynamicModel;
use app\classes\BaseController;
use app\models\important_events\ImportantEvents;

class ReportController extends BaseController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ImportantEvents;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
        ]);
    }

    /**
     * @return []
     * @throws FormValidationException
     * @throws RecordNotFound
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSetComment()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = DynamicModel::validateData(Yii::$app->request->post(), [
            ['id', 'integer'],
            ['id', 'required'],
            ['comment', 'string'],
        ]);

        if ($data->hasErrors()) {
            throw new FormValidationException($data);
        }

        $event = ImportantEvents::findOne($data->id);
        if (is_null($event)) {
            throw new RecordNotFound;
        }

        $event->comment = $data->comment;
        $event->update($runValidation = false);

        return ['response' => 'success'];
    }

}