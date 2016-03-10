<?php

namespace app\controllers\api\internal;

use Yii;
use DateTime;
use app\exceptions\web\NotImplementedHttpException;
use app\exceptions\api\internal\ExceptionValidationForm;
use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\UsageVoipValidator;
use app\models\billing\Calls;

class VoipController extends ApiInternalController
{

    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Definition(
     *   definition="call",
     *   type="object",
     *   required={"id", "connect_time", "src_number", "dst_number", "direction", "length", "cost", "rate"},
     *   @SWG\Property(property="id",type="integer",description="идентификатор звонка"),
     *   @SWG\Property(property="connect_time",type="date",description="дата начала звонка"),
     *   @SWG\Property(property="src_number",type="string",description="номер А"),
     *   @SWG\Property(property="dst_number",type="string",description="номер Б"),
     *   @SWG\Property(property="direction",type="string",description="направление"),
     *   @SWG\Property(property="length",type="integer",description="длительность звонка"),
     *   @SWG\Property(property="cost",type="number",description="стоимость звонка"),
     *   @SWG\Property(property="rate",type="number",description="стоимость минуты разговора")
     * ),
     * @SWG\Post(
     *   tags={"Работа со звонками"},
     *   path="/internal/voip/calls/",
     *   summary="Получение списка звонков",
     *   operationId="Получение списка звонков",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="number",type="string",description="номер телефона",in="formData"),
     *   @SWG\Parameter(name="year",type="integer",description="год, за который выбирать звонки",in="formData"),
     *   @SWG\Parameter(name="month",type="integer",description="месяц, за который выбирать звонки",in="formData"),
     *   @SWG\Parameter(name="offset",type="integer",description="сдвиг в выборке записей",in="formData"),
     *   @SWG\Parameter(name="limit",type="integer",description="размер выборки",in="formData",maximum="10000"),
     *   @SWG\Response(
     *     response=200,
     *     description="данные о клиентах партнёра",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/call"
     *       )
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
    public function actionCalls()
    {
        $requestData = $this->requestData;

        $model = DynamicModel::validateData(
            $requestData,
            [
                [['account_id', 'offset', 'limit', 'year', 'month'], 'integer'],
                ['number', 'trim'],
                ['year', 'default', 'value' => (new DateTime())->format('Y')],
                ['month', 'default', 'value' => (new DateTime())->format('m')],
                ['offset', 'default', 'value' => 0],
                ['limit', 'default', 'value' => 1000],
                ['account_id', AccountIdValidator::className()],
                ['number', UsageVoipValidator::className(), 'account_id_field' => 'account_id'],
            ]
        );

        if ($model->hasErrors()) {
            throw new ExceptionValidationForm($model);
        }

        return
            Calls::dao()->getCalls(
                $model->account_id,
                $model->number,
                $model->year,
                $model->month,
                $model->offset,
                $model->limit
            );
    }

}