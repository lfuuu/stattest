<?php

namespace app\controllers\api\internal;

use app\classes\api\ApiPhone;
use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use Yii;
use DateTime;
use app\exceptions\web\NotImplementedHttpException;
use app\exceptions\api\internal\ExceptionValidationForm;
use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\UsageVoipValidator;
use app\models\billing\CallsRaw;
use yii\base\InvalidParamException;

class VoipController extends ApiInternalController
{

    /**
     * @throws NotImplementedHttpException
     */
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
     * @throws \yii\base\InvalidConfigException
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
                ['account_id', AccountIdValidator::class],
                ['number', UsageVoipValidator::class, 'account_id_field' => 'account_id'],
            ]
        );

        if ($model->hasErrors()) {
            throw new ExceptionValidationForm($model);
        }

        $result = [];
        foreach (
            CallsRaw::dao()->getCalls(
                $model->account_id,
                $model->number,
                $model->year,
                $model->month,
                $model->offset,
                $model->limit
            ) as $call
        ) {
            $call['cost'] = (double)$call['cost'];
            $call['rate'] = (double)$call['rate'];
            $result[] = $call;
        }

        return $result;
    }

    /**
     * @SWG\Get(tags = {"Numbers"}, path = "/internal/voip/number-info/", summary = "Получение информации о номере", operationId = "voip_number_info",
     *   @SWG\Parameter(name = "number", type = "string", description = "номер телефона", in = "query", default = "", required = true),
     *   @SWG\Response(response = 200, description = "Получение информации о номере",
     *     @SWG\Schema(type = "object", required = {"account_balance", "account_balance_available", "subaccount_balance"},
     *       @SWG\Property(property = "number", type = "string"),
     *       @SWG\Property(property = "ndc", type = "number"),
     *       @SWG\Property(property = "ndc_type", type = "number"),
     *       @SWG\Property(property = "country_name", type = "string"),
     *       @SWG\Property(property = "region_name", type = "string"),
     *       @SWG\Property(property = "city_name", type = "string"),
     *       @SWG\Property(property = "operator_name", type = "string"),
     *       @SWG\Property(property = "number_length", type = "number")
     *       )
     *     )
     *   )
     * )
     */
    public function actionNumberInfo()
    {
        $requestData = Yii::$app->request->get();

        $model = DynamicModel::validateData(
            $requestData,
            [
                ['number', 'string'],
                ['number', 'trim'],
                ['number', 'required'],
            ]
        );

        if ($model->hasErrors()) {
            throw new ModelValidationException($model);
        }

        $number = Number::findOne(['number' => $model['number']]);

        if (!$number) {
            throw new InvalidParamException('Номер не найден');
        }

        return array_merge(
            ['number' => $number->number],
            NumberRange::getNumberInfo($number)
        );
    }

    /**
     * @SWG\Get(tags = {"Numbers"}, path = "/internal/voip/get-mvno-number-list/", summary = "Получение списка MVNO номеров", operationId = "get-mvno-number-list",
     *   @SWG\Parameter(name = "is_active", type = "integer", description = "Только активные (или все)", in = "query", default = 0, required = false),
     *   @SWG\Response(response = 200, description = "данные о клиентах партнёра",
     *     @SWG\Schema(type = "array", @SWG\Items(type = "string", description = "Номер телефона"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     */
    public function actionGetMvnoNumberList()
    {
        $requestData = Yii::$app->request->get();

        $isActive = isset($requestData['is_active']) ? (bool)(int)$requestData['is_active'] : false;

        $query = Number::find()
            ->where(['ndc_type_id' => NdcType::ID_MOBILE])
            ->select('number')
            ->orderBy(['number' => SORT_ASC]);

        $isActive && $query->andWhere(['status' => Number::$statusGroup[Number::STATUS_GROUP_ACTIVE]]);

        return $query
            ->asArray()
            ->column();
    }
}