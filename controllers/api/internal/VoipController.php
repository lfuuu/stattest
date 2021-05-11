<?php

namespace app\controllers\api\internal;

use app\classes\Assert;
use app\classes\HttpClient;
use app\dao\billing\CallsDao;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\DataRaw;
use app\models\billing\SmscRaw;
use app\models\ClientAccount;
use app\models\filter\SmsFilter;
use app\models\Number;
use app\modules\nnp\models\AccountTariffLight;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use app\modules\uu\models\Tariff;
use Yii;
use DateTime;
use app\exceptions\web\NotImplementedHttpException;
use app\exceptions\api\internal\ExceptionValidationForm;
use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\UsageVoipValidator;
use app\models\billing\CallsRaw;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\Expression;

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
     *   tags={"Статистика"},
     *   path="/internal/voip/calls/",
     *   summary="Получение списка звонков",
     *   operationId="Получение списка звонков",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData",default=""),
     *   @SWG\Parameter(name="number",type="string",description="номер телефона",in="formData",default=""),
     *   @SWG\Parameter(name="year",type="integer",description="год, за который выбирать звонки",in="formData",default=""),
     *   @SWG\Parameter(name="month",type="integer",description="месяц, за который выбирать звонки",in="formData",default=""),
     *   @SWG\Parameter(name="day",type="integer",description="день, за который выбирать звонки",in="formData",default=""),
     *   @SWG\Parameter(name="offset",type="integer",description="сдвиг в выборке записей",in="formData",default="0"),
     *   @SWG\Parameter(name="limit",type="integer",description="размер выборки",in="formData",maximum="10000",default="100"),
     *   @SWG\Parameter(name="is_with_nnp_info",type="integer",description="с NNP информацией",in="formData",default="0"),
     *   @SWG\Parameter(name="from_datetime",type="string",description="Время начала (по TZ-клиента) дата или дата-время",in="formData",default=""),
     *   @SWG\Parameter(name="to_datetime",type="string",description="Время окончания (по TZ-клиента)  дата или дата-время",in="formData",default=""),
     *   @SWG\Parameter(name="is_in_utc",type="string",description="Дата в параметрах и данных в UTC, иначе в TZ клиента",in="formData",default="1"),
     *   @SWG\Parameter(name="is_with_general_info",type="string",description="Подказывать общую информацию",in="formData",default="0"),
     *   @SWG\Parameter(name="is_with_nnp_info",type="string",description="Добавить ННП информацию",in="formData",default="0"),
     *   @SWG\Parameter(name="is_with_tariff_info",type="string",description="Добавить информацию о тарифе",in="formData",default="0"),
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

        $dateTimeRegexp = '/^(\d{4}-\d{2}-\d{2})( \d{2}:\d{2}:\d{2})?$/';
        $dateTimeStrongRegexp = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

        $model = DynamicModel::validateData(
            $requestData,
            [
                [['account_id', 'offset', 'limit', 'year', 'month', 'day', 'is_with_nnp_info', 'is_in_utc', 'is_with_general_info', 'is_with_nnp_info', 'is_with_tariff_info'], 'integer'],
                ['number', 'trim'],
                ['year', 'default', 'value' => (new DateTime())->format('Y')],
                ['month', 'default', 'value' => (new DateTime())->format('m')],
                ['offset', 'default', 'value' => 0],
                ['limit', 'default', 'value' => 1000000],
                ['is_in_utc', 'default', 'value' => 1],
                ['is_with_general_info', 'default', 'value' => 0],
                ['is_with_tariff_info', 'default', 'value' => 0],
                ['from_datetime', 'match', 'pattern' => $dateTimeRegexp],
                ['to_datetime', 'match', 'pattern' => $dateTimeRegexp],
                ['account_id', AccountIdValidator::class],
                ['number', UsageVoipValidator::class, 'account_id_field' => 'account_id', 'skipOnEmpty' => true],
            ]
        );

        if ($model->hasErrors()) {
            throw new ExceptionValidationForm($model);
        }

        if (($model->from_datetime || $model->to_datetime) && (!$model->from_datetime || !$model->to_datetime)) {
            throw new InvalidParamException('fields from_datetime and to_datetime must be filled');
        }

        $clientAccount = ClientAccount::findOne(['id' => $model->account_id]);
        Assert::isObject($clientAccount, 'ClientAccount#' . $model->account_id);

        if (!$model->offset || $model->offset < 0) {
            $model->offset = 0;
        }
        $model->offset = (int)$model->offset;

        if ($model->limit && $model->limit < 0) {
            $model->limit = 0;
        }
        $model->limit = (int)$model->limit;


        $utcTz = (new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $tz = $model->is_in_utc ? $utcTz : $clientAccount->timezone;

        if ($model->from_datetime && $model->to_datetime) {

            if (!preg_match($dateTimeStrongRegexp, $model->from_datetime)) {
                $model->from_datetime .= ' 00:00:00';
            }

            if (!preg_match($dateTimeStrongRegexp, $model->to_datetime)) {
                $model->to_datetime = (new \DateTimeImmutable($model->to_datetime))
                    ->modify('+1 day')
                    ->setTime(0, 0, 0)
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);
            }

            $firstDayOfDate = (new \DateTimeImmutable($model->from_datetime, $tz));
            $lastDayOfDate = (new \DateTimeImmutable($model->to_datetime, $tz));

        } else {
            $firstDayOfDate = (new \DateTimeImmutable('now', $tz))
                ->setDate($model->year, $model->month, $model->day ?: 1)
                ->setTime(0, 0, 0);

            $lastDayOfDate = $firstDayOfDate->modify('+1 day');

            !$model->day && $lastDayOfDate = $lastDayOfDate->modify('last day of this month');
        }

        $diff = $firstDayOfDate->diff($lastDayOfDate);

        if ($diff->y > 0 || $diff->m > 2 || ($diff->m > 1 && $diff->d > 2)){
            throw new \InvalidArgumentException('DATETIME_RANGE_LIMIT', -10);
        }


        $query = CallsRaw::dao()->getCalls(
            $clientAccount,
            $model->number,
            $firstDayOfDate,
            $lastDayOfDate
        );

        if ($model->is_with_tariff_info) {
            $query->leftJoin(['l' => AccountTariffLight::tableName()], 'l.id = cr.account_tariff_light_id');
            $query->addSelect(['l.tariff_id']);
        }

        $generalInfo = [];

        if ($model->is_with_general_info) {
            $sumQuery = clone $query;
            $sumQuery->select([
                'sum' => new Expression('SUM(-cost)::decimal(12,2)'),
                'count' => new Expression('COUNT(*)')
            ]);
            $sumQuery->orderBy(null);

            $generalInfo = $sumQuery->one(CallsRaw::getDb());

            $generalInfo['sum'] = (float)$generalInfo['sum'];
        }

        if ($model->offset) {
            $query->offset($model->offset);
        }

        $limit = $model->limit > CallsDao::CALLS_MAX_LIMIT ? CallsDao::CALLS_MAX_LIMIT : $model->limit;

        $generalInfo['offset'] = $model->offset;
        $generalInfo['limit'] = $limit;

        $query->limit($limit);

        static $cTariff = [];

        foreach ($query->each(100, CallsRaw::getDb()) as $call) {
            $call['cost'] = (double)$call['cost'];
            $call['rate'] = (double)$call['rate'];

            if ($model->is_with_nnp_info) {
                $call['nnp']['src'] = $this->_getNnpInfo($call['src_number']);
                $call['nnp']['dst'] = $this->_getNnpInfo($call['dst_number']);
            }

            if ($model->is_with_tariff_info) {
                $tariffName = null;
                if ($call['tariff_id']) {
                    if (isset($cTariff[$call['tariff_id']])) {
                        $tariffName = $cTariff[$call['tariff_id']];
                    } else {
                        $tariffName = Tariff::find()->where(['id' => $call['tariff_id']])->select(['name'])->scalar();
                        $cTariff[$call['tariff_id']] = $tariffName;
                    }
                }

                $call['tariff_name'] = $tariffName;
                unset($call['tariff_id']);
            }

            $result[] = $call;
        }

        $result = $result ? $result : [];

        return $model->is_with_general_info ? ['info' => $generalInfo, 'calls' => $result] : $result;
    }

    private function _getNnpInfo($number)
    {
        if (!$number) {
            return null;
        }

        $redis = \Yii::$app->redis;

        if ($numberInfo = $redis->get('numberInfo:' . $number)) {
            return unserialize($numberInfo);
        }

        $url = isset(\Yii::$app->params['nnpInfoServiceURL']) && \Yii::$app->params['nnpInfoServiceURL'] ? \Yii::$app->params['nnpInfoServiceURL'] : false;

        if (!$url) {
            throw new InvalidConfigException('nnpInfoServiceURL not set');
        }


        $numberInfo = [
            'nnp_city_id' => 0,
            'nnp_region_id' => 0,
            'nnp_operator_id' => 0,
            'ndc_type_id' => 0,
        ];

        try {
            $numberInfo = (new HttpClient())
                ->get($url, [
                    'cmd' => 'getNumberRangeByNum',
                    'num' => $number])
                ->getResponseDataWithCheck();
        } catch (\Exception $e) {
            Yii::error($e);
        }

        $redis = \Yii::$app->redis;

        $data = [
            'country_name' => $redis->get('country:' . $numberInfo['country_code']) ?: 'unknown',
            'city_name' => $redis->get('city:' . $numberInfo['nnp_city_id']) ?: 'unknown',
            'region_name' => $redis->get('region:' . $numberInfo['nnp_region_id']) ?: 'unknown',
            'operator_name' => $redis->get('operator:' . $numberInfo['nnp_operator_id']) ?: 'unknown',
            'ndc_type_name' => $redis->get('ndcType:' . $numberInfo['ndc_type_id']) ?: 'unknown',
        ];

        $redis->set('numberInfo:' . $number, serialize($data));

        return $data;
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


    /**
     * @SWG\Definition(
     *   definition="data",
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
     *   tags={"Статистика"},
     *   path="/internal/voip/data/",
     *   summary="Мобильный интернет",
     *   operationId="Мобильный интернет",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData",default=""),
     *   @SWG\Parameter(name="number",type="string",description="номер телефона",in="formData",default=""),
     *   @SWG\Parameter(name="from_datetime",type="string",description="Время начала (по TZ-клиента) дата или дата-время",in="formData",default=""),
     *   @SWG\Parameter(name="to_datetime",type="string",description="Время окончания (по TZ-клиента)  дата или дата-время",in="formData",default=""),
     *   @SWG\Parameter(name="is_in_utc",type="string",description="Дата в параметрах и данных в UTC, иначе в TZ клиента",in="formData",default="1"),
     *   @SWG\Parameter(name="group_by",type="string",description="Групировать по",in="formData",default="none",enum={"none", "number", "year", "month", "day", "hour"}),
     *   @SWG\Parameter(name="offset",type="integer",description="сдвиг в выборке записей",in="formData",default="0"),
     *   @SWG\Parameter(name="limit",type="integer",description="размер выборки",in="formData",maximum="10000",default="100"),
     *   @SWG\Response(
     *     response=200,
     *     description="данные о клиентах партнёра",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/data"
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
    public function actionData()
    {

        $requestData = $this->requestData;

        $dateTimeRegexp = '/^(\d{4}-\d{2}-\d{2})( \d{2}:\d{2}:\d{2})?$/';
        $dateTimeStrongRegexp = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

        $model = DynamicModel::validateData(
            $requestData,
            [
                [['account_id', 'offset', 'limit', 'is_in_utc'], 'integer'],
                ['number', 'trim'],
                ['offset', 'default', 'value' => 0],
                ['limit', 'default', 'value' => 1000],
                ['is_in_utc', 'default', 'value' => 1],
                ['from_datetime', 'match', 'pattern' => $dateTimeRegexp],
                ['to_datetime', 'match', 'pattern' => $dateTimeRegexp],
                ['account_id', AccountIdValidator::class],
                ['number', UsageVoipValidator::class, 'account_id_field' => 'account_id', 'skipOnEmpty' => true],
                ['group_by', 'in', 'range' => ['', 'none', 'number', 'year', 'month', 'day', 'hour', '__country']],
            ]
        );

        if ($model->hasErrors()) {
            throw new ExceptionValidationForm($model);
        }

        if (($model->from_datetime || $model->to_datetime) && (!$model->from_datetime || !$model->to_datetime)) {
            throw new \InvalidArgumentException('fields from_datetime and to_datetime must be filled');
        }

        $clientAccount = ClientAccount::findOne(['id' => $model->account_id]);

        $utcTz = (new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $tz = $model->is_in_utc ? $utcTz : $clientAccount->timezone;


        if (!preg_match($dateTimeStrongRegexp, $model->from_datetime)) {
            $model->from_datetime .= ' 00:00:00';
        }

        if (!preg_match($dateTimeStrongRegexp, $model->to_datetime)) {
            $model->to_datetime = (new \DateTimeImmutable($model->to_datetime))
                ->modify('+1 day')
                ->setTime(0, 0, 0)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }

        $firstDayOfDate = (new \DateTimeImmutable($model->from_datetime, $tz));
        $lastDayOfDate = (new \DateTimeImmutable($model->to_datetime, $tz));

        $diff = $firstDayOfDate->diff($lastDayOfDate);

        if ($diff->y > 0 || $diff->m > 2 || ($diff->m > 1 && $diff->d > 2)){
            throw new \InvalidArgumentException('DATETIME_RANGE_LIMIT', -10);
        }

        $query = DataRaw::dao()->getData(
            $clientAccount,
            $model->number,
            $firstDayOfDate,
            $lastDayOfDate,
            $model->offset,
            $model->limit,
            $model->group_by
        );

        $result = [];
        foreach ($query->each(100, DataRaw::getDb()) as $data) {
            $data['cost'] = (double)$data['cost'];

            if (isset($data['rate'])) {
                $data['rate'] = (double)$data['rate'];
            }

            $result[] = $data;
        }

        return $result;

    }


    /**
     * @SWG\Definition(
     *   definition="sms",
     *   type="object",
     *   required={"id", "setup_time", "src_number", "dst_number", "cost", "rate", "parts", "count"},
     *   @SWG\Property(property="id",type="integer",description="идентификатор"),
     *   @SWG\Property(property="setup_time",type="date",description="дата отправки SMS"),
     *   @SWG\Property(property="src_number",type="string",description="номер А"),
     *   @SWG\Property(property="dst_number",type="string",description="номер Б"),
     *   @SWG\Property(property="cost",type="number",description="стоимость"),
     *   @SWG\Property(property="rate",type="number",description="ставка"),
     *   @SWG\Property(property="parts",type="number",description="кол-во частй в SMS"),
     *   @SWG\Property(property="count",type="integer",description="кол-во SMS")
     * ),
     * @SWG\Post(
     *   tags={"Статистика"},
     *   path="/internal/voip/sms/",
     *   summary="SMS",
     *   operationId="SMS",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData",default=""),
     *   @SWG\Parameter(name="number",type="string",description="номер телефона",in="formData",default=""),
     *   @SWG\Parameter(name="from_datetime",type="string",description="Время начала (по TZ-клиента) дата или дата-время",in="formData",default=""),
     *   @SWG\Parameter(name="to_datetime",type="string",description="Время окончания (по TZ-клиента)  дата или дата-время",in="formData",default=""),
     *   @SWG\Parameter(name="is_in_utc",type="string",description="Дата в параметрах и данных в UTC, иначе в TZ клиента",in="formData",default="1"),
     *   @SWG\Parameter(name="group_by",type="string",description="Групировать по",in="formData",default="none",enum={"none", "year", "month", "day", "hour"}),
     *   @SWG\Parameter(name="offset",type="integer",description="сдвиг в выборке записей",in="formData",default="0"),
     *   @SWG\Parameter(name="limit",type="integer",description="размер выборки",in="formData",maximum="10000",default="100"),
     *   @SWG\Response(
     *     response=200,
     *     description="данные о SMS",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/sms"
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
    public function actionSms()
    {
        $requestData = $this->requestData;

        $searchModel = new SmsFilter();
        $searchModel->load($requestData, '') && $searchModel->validate();

        if ($searchModel->hasErrors()) {
            throw new ExceptionValidationForm($searchModel);
        }

        $query = $searchModel->search();

        $result = [];
        foreach ($query->each(100, SmscRaw::getDb()) as $data) {
            $data['cost'] = abs((double)$data['cost']);

            if (isset($data['rate'])) {
                $data['rate'] = abs((double)$data['rate']);
            }

            $result[] = $data;
        }

        return $result;
    }
}