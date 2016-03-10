<?php

namespace app\controllers\api;

use Yii;
use app\exceptions\FormValidationException;
use app\classes\DynamicModel;
use app\classes\ApiInternalController;
use app\models\important_events\ImportantEvents;

class ImportantEventsController extends ApiInternalController
{

    /**
     * @SWG\Post(
     *   tags={"Работа со значимыми событиями"},
     *   path="/important-events/add/",
     *   summary="Добавление значимого события в лог",
     *   operationId="Добавление значимого события в лог",
     *   @SWG\Parameter(name="event",type="string",description="событие",in="formData",required=true),
     *   @SWG\Parameter(name="source",type="string",description="источник",in="formData",required=true),
     *   @SWG\Parameter(name="client_id",type="integer",description="идентификатор лицевого счёта для которого это событие произошло",in="formData",required=true),
     *   @SWG\Parameter(name="extends_data",type="string",description="объект с дополнительными данными",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="результат работы метода",
     *     @SWG\Definition(
     *       type="object",
     *       required={"success"},
     *       @SWG\Property(property="success",type="boolean",description="результат работы метода",default="true")
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
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
            throw new FormValidationException($model);
        }

        if (ImportantEvents::create($model->event, $model->source, (array) $data)) {
            return ['success' => true];
        }
    }

}