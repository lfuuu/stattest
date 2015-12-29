<?php

namespace app\controllers\api\internal;

use Yii;
use DateTime;
use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\exceptions\web\NotImplementedHttpException;
use app\exceptions\FormValidationException;
use app\models\billing\Calls;

class VoipController extends ApiInternalController
{

    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    public function actionCalls()
    {
        $requestData = $this->requestData;

        $model = DynamicModel::validateData(
            $requestData,
            [
                [['account_id', 'offset', 'limit', 'year', 'month'], 'integer'],
                ['number', 'trim'],
                [['account_id', 'number'], 'required'],
                ['year', 'default', 'value' => (new DateTime())->format('Y')],
                ['month', 'default', 'value' => (new DateTime())->format('m')],
                ['offset', 'default', 'value' => 0],
                ['limit', 'default', 'value' => 1000],
            ]
        );

        if ($model->hasErrors()) {
            throw new FormValidationException($model);
        }

        return Calls::dao()->getCalls(
            $model->account_id,
            $model->number,
            $model->year,
            $model->month,
            $model->offset,
            $model->limit
        );
    }

}