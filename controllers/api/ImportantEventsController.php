<?php

namespace app\controllers\api;

use Yii;
use app\exceptions\api\internal\ExceptionValidationForm;
use app\classes\DynamicModel;
use app\classes\ApiInternalController;
use app\models\important_events\ImportantEvents;

class ImportantEventsController extends ApiInternalController
{

    public function actionAdd()
    {
        $data = $this->getRequestParams();

        $model = DynamicModel::validateData(
            $data,
            [
                [['event', 'source'], 'required'],
                [['event', 'source'], 'string'],
            ]
        );

        if ($model->hasErrors()) {
            throw new ExceptionValidationForm($model);
        }

        if (ImportantEvents::create($model->event, $model->source, (array) $data)) {
            return ['success' => true];
        }
    }

}