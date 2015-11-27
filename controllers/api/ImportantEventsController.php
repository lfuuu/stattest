<?php

namespace app\controllers\api;

use Yii;
use app\exceptions\FormValidationException;
use app\classes\DynamicModel;
use app\classes\ApiController;
use app\models\ImportantEvents;

class ImportantEventsController extends ApiController
{

    public function actionAdd()
    {
        $data = Yii::$app->request->bodyParams;

        $model = DynamicModel::validateData(
            $data,
            [
                [['event'], 'required'],
                [['event'], 'string'],
            ]
        );

        if ($model->hasErrors()) {
            throw new FormValidationException($model);
        }

        if (ImportantEvents::create($model->event, (array) $data)) {
            return ['success' => true];
        }
    }

}