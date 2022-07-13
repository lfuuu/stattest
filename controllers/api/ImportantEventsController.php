<?php

namespace app\controllers\api;

use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\exceptions\ModelValidationException;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\light_models\ImportantEventLight;
use Yii;

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
     *   @SWG\Parameter(name="client_id",type="string",description="ID клиента",in="formData",required=false),
     *   @SWG\Parameter(name="value",type="string",description="текущее состояние счета",in="formData",required=false),
     *   @SWG\Parameter(name="limit",type="string",description="лимит",in="formData",required=false),
     *   @SWG\Parameter(name="*",type="string",description="дополнительные параметры",in="formData",required=false),
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
    /**
     * @return array
     * @throws ModelValidationException
     * @throws \yii\base\InvalidConfigException
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
            throw new ModelValidationException($model);
        }

        if (ImportantEvents::create($model->event, $model->source, (array)$data)) {
            return ['success' => true];
        }
    }

    /**
     * @SWG\Post(
     *   tags={"Работа со значимыми событиями"},
     *   path="/important-events/get/",
     *   summary="Получение списка значимых событий",
     *   operationId="Получение списка значимых событий",
     *   @SWG\Parameter(name="clientId[]",type="integer",description="ID лицевых счетов",in="formData"),
     *   @SWG\Parameter(name="clientId[]",type="integer",description="ID лицевых счетов",in="formData"),
     *   @SWG\Parameter(name="event[]",type="string",description="код события",in="formData"),
     *   @SWG\Parameter(name="event[]",type="string",description="код события",in="formData"),
     *   @SWG\Parameter(name="id",type="string",description="ID события (формат: ((<|>)?=?)(\d+)",in="formData"),
     *   @SWG\Parameter(name="date[]",type="string",description="даты за которые произошли события",in="formData"),
     *   @SWG\Parameter(name="date[]",type="string",description="даты за которые произошли события",in="formData"),
     *   @SWG\Parameter(name="limit",type="string",description="лимит",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="результат работы метода",
     *     @SWG\Definition(
     *       type="object",
     *       @SWG\Property(property="id", type="integer", description="ID события"),
     *       @SWG\Property(property="client_id", type="integer", description="ID лицевого счета"),
     *       @SWG\Property(property="source_id", type="integer", description="ID источника события (table: important_events_sources)"),
     *       @SWG\Property(property="date", type="string", description="Дата события"),
     *       @SWG\Property(property="event", type="string", description="Код события"),
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
    /**
     * @return array
     * @throws ModelValidationException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGet()
    {
        $rules = [
            'id' => '#^((?:<|>)?=?)(\d+)$#i',
            'date' => '#^(\d{4}\-\d{2}\-\d{2})$#',
            'event' => '#^[a-zA-Z0-9_\-]+$#',
        ];

        $model = DynamicModel::validateData(
            Yii::$app->request->post(),
            [
                ['id', 'match', 'pattern' => $rules['id']],
                ['date', 'each', 'rule' => ['match', 'pattern' => $rules['date']]],
                [['client_id',], 'each', 'rule' => ['integer']],
                ['event', 'each', 'rule' => ['match', 'pattern' => $rules['event']]],
                ['limit', 'integer'],
            ]
        );

        if ($model->hasErrors()) {
            throw new ModelValidationException($model);
        }

        $result = ImportantEvents::find()->orderBy([
            'id' => SORT_ASC,
        ]);
        $applyFilter = false;

        if ($model->id) {
            preg_match($rules['id'], $model->id, $matches);
            list(,$condition, $value) = $matches;

            if (!$condition) {
                $condition = '=';
            }

            $result->andWhere([$condition, 'id', $value]);
            $applyFilter = true;
        }

        if ($model->client_id) {
            $result->andWhere(['client_id' => $model->client_id]);
            $applyFilter = true;
        }

        if ($model->event) {
            $result->andWhere(['event' => $model->event]);
            $applyFilter = true;
        }

        switch(count($model->date)) {
            case 2: {
                $result->andWhere(['BETWEEN', 'date', $model->date[0], $model->date[1]]);
                $applyFilter = true;
                break;
            }
            case 1: {
                $result->andWhere(['>=', 'date', $model->date[0]]);
                $applyFilter = true;
                break;
            }
        }

        if (!$applyFilter) {
            $result->limit(1);
        } else {
            if ((int)$model->limit) {
                $result->limit($model->limit);
            }
        }

        return array_map(function(ImportantEvents $event) {
            $formattedResult = new ImportantEventLight;
            $formattedResult->setAttributes($event->getAttributes() + [
                'country_code' => $event->clientAccount ? $event->clientAccount->country->code : null,
            ]);

            if (($prop = $event->getProperties()) && isset($prop['login_email']) && $prop['login_email']) {
                $formattedResult->login_email = $prop['login_email'];
            }
            return $formattedResult;
        }, $result->all());
    }

    /**
     * @SWG\Get(
     *   tags={"Работа со значимыми событиями"},
     *   path="/important-events/get-names/",
     *   summary="Получение списка названий значимых событий",
     *   operationId="Получение списка названий значимых событий",
     *   @SWG\Response(
     *     response=200,
     *     description="результат работы метода",
     *     @SWG\Definition(
     *       type="object",
     *       @SWG\Property(property="id", type="integer", description="ID названия"),
     *       @SWG\Property(property="code", type="string", description="Код события"),
     *       @SWG\Property(property="value", type="string", description="Название события"),
     *       @SWG\Property(property="group_id", type="integer", description="ID группы событий"),
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
    /**
     * @return ImportantEventsNames[]
     */
    public function actionGetNames()
    {
        return ImportantEventsNames::find()->all();
    }

}