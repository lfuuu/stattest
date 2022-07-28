<?php

namespace app\controllers\api\internal;

use app\models\billing\api\ApiRaw;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use Yii;
use app\exceptions\web\NotImplementedHttpException;
use app\exceptions\api\internal\ExceptionValidationForm;
use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\classes\validators\AccountIdValidator;
use app\models\billing\A2pSms;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;

class StatController extends ApiInternalController
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
     *   definition="api",
     *   type="object",
     *   required={"id", "connect_time", "cost", "rate"},
     *   @SWG\Property(property="id",type="integer",description="идентификатор"),
     *   @SWG\Property(property="connect_time",type="date",description="дата подключения"),
     *   @SWG\Property(property="account_id",type="integer",description="идентификатор аккаунта"),
     *   @SWG\Property(property="api_id",type="integer",description="идентификатор api"),
     *   @SWG\Property(property="api_method_id",type="integer",description="идентификатор api метода"),
     *   @SWG\Property(property="service_api_id",type="integer",description="идентификатор api сервиса"),
     *   @SWG\Property(property="api_count",type="integer",description="количество api"),
     *   @SWG\Property(property="api_weight",type="number",description="вес api"),
     *   @SWG\Property(property="unique_id",type="string",description="уникальный идентификатор"),
     *   @SWG\Property(property="cost",type="number",description="стоимость"),
     *   @SWG\Property(property="rate",type="number",description="ставка"),
     * ),
     * @SWG\Post(
     *   tags={"Статистика"},
     *   path="/internal/stat/api/",
     *   summary="api",
     *   operationId="api",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData",default=""),
     *   @SWG\Parameter(name="from_datetime",type="string",description="Время начала (по TZ-клиента) дата или дата-время",in="formData",default=""),
     *   @SWG\Parameter(name="to_datetime",type="string",description="Время окончания (по TZ-клиента)  дата или дата-время",in="formData",default=""),
     *   @SWG\Parameter(name="is_in_utc",type="string",description="Дата в параметрах и данных в UTC, иначе в TZ клиента",in="formData",default="1"),
     *   @SWG\Parameter(name="offset",type="integer",description="сдвиг в выборке записей",in="formData",default="0"),
     *   @SWG\Parameter(name="limit",type="integer",description="размер выборки",in="formData",maximum="1000000",default="100"),
     *   @SWG\Parameter(name="unique_id",type="string",description="уникальный id",in="formData", default=""),
     *   @SWG\Parameter(name="group_by",type="string",description="Групировать по",in="formData",default="none",enum={"none", "year", "month", "day", "hour", "minute"}),
     *   @SWG\Parameter(name="is_with_general_info",type="string",description="Подказывать общую информацию",in="formData",default="0"),
     * @SWG\Response(
     *     response=200,
     *     description="Статистика по вызовам API",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/api"
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
    public function actionApi()
    {
        $requestData = $this->requestData;

        $dateTimeRegexp = '/^(\d{4}-\d{2}-\d{2})( \d{2}:\d{2}:\d{2})?$/';
        $dateTimeStrongRegexp = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        
        $model = DynamicModel::validateData(
            $requestData,
            [
                [['account_id', 'offset', 'limit', 'is_in_utc', 'is_with_general_info'], 'integer'],
                [['unique_id'], 'string'],
                [['offset', 'is_with_general_info'], 'default', 'value' => 0],
                ['limit', 'default', 'value' => 1000000],
                ['is_in_utc', 'default', 'value' => 1],
                ['from_datetime', 'match', 'pattern' => $dateTimeRegexp],
                ['to_datetime', 'match', 'pattern' => $dateTimeRegexp],
                ['account_id', AccountIdValidator::class],
                ['group_by', 'in', 'range' => ['', 'none', 'number', 'year', 'month', 'day', 'hour', 'minute']],
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
        $uniqueId = $model->unique_id;

        $diff = $firstDayOfDate->diff($lastDayOfDate);

        if ($diff->y > 0 || $diff->m > 2 || ($diff->m > 1 && $diff->d > 2)){
            throw new \InvalidArgumentException('DATETIME_RANGE_LIMIT', -10);
        }

        $query = ApiRaw::dao()->getData(
            $clientAccount,
            $firstDayOfDate,
            $lastDayOfDate,
            $uniqueId,
            $model->offset,
            $model->limit,
            $model->group_by,
        );

        $result = [];
        foreach ($query->each(500, ApiRaw::getDb()) as $data) {
            $data['cost'] = (double)$data['cost'];

            if (isset($data['rate'])) {
                $data['rate'] = (double)$data['rate'];
            }

            $result[] = $data;
        }

        if ($model->is_with_general_info) {
            return $this->makeGeneralInfo($result, $query, ApiRaw::getDb());
        }

        return $result;
    }


     /**
    * @SWG\Post(
    *   tags={"Статистика"},
    *   path="/internal/stat/a2p/",
    *   summary="a2p",
    *   operationId="a2p",
    *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData",default=""),
    *   @SWG\Parameter(name="from_datetime",type="string",description="Время начала (по TZ-клиента) дата или дата-время",in="formData",default=""),
    *   @SWG\Parameter(name="to_datetime",type="string",description="Время окончания (по TZ-клиента)  дата или дата-время",in="formData",default=""),
    *   @SWG\Parameter(name="is_in_utc",type="string",description="Дата в параметрах и данных в UTC, иначе в TZ клиента",in="formData",default="1"),
    *   @SWG\Parameter(name="offset",type="integer",description="сдвиг в выборке записей",in="formData",default="0"),
    *   @SWG\Parameter(name="limit",type="integer",description="размер выборки",in="formData",maximum="1000000",default="100"),
    *   @SWG\Parameter(name="group_by",type="string",description="Групировать по",in="formData",default="none",enum={"none", "year", "month", "day", "hour", "minute"}),
    *   @SWG\Parameter(name="is_with_general_info",type="string",description="Подказывать общую информацию",in="formData",default="0"),
    * @SWG\Response(
    *     response=200,
    *     description="Статистика по A2P SMS",
    *     @SWG\Schema(
    *       type="array",
    *       @SWG\Items(
    *         ref="#/definitions/api"
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
    public function actionA2p()
    {
        $requestData = $this->requestData;

        $dateTimeRegexp = '/^(\d{4}-\d{2}-\d{2})( \d{2}:\d{2}:\d{2})?$/';
        $dateTimeStrongRegexp = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        
        $model = DynamicModel::validateData(
            $requestData,
            [
                [['account_id', 'offset', 'limit', 'is_in_utc', 'is_with_general_info'], 'integer'],
                [['offset', 'is_with_general_info'], 'default', 'value' => 0],
                ['limit', 'default', 'value' => 1000000],
                ['is_in_utc', 'default', 'value' => 1],
                ['from_datetime', 'match', 'pattern' => $dateTimeRegexp],
                ['to_datetime', 'match', 'pattern' => $dateTimeRegexp],
                ['account_id', AccountIdValidator::class],
                ['group_by', 'in', 'range' => ['', 'none', 'number', 'year', 'month', 'day', 'hour', 'minute']],
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

        $query = A2pSms::dao()->getData(
            $clientAccount,
            $firstDayOfDate,
            $lastDayOfDate,
            $model->offset,
            $model->limit,
            $model->group_by
        );
        
        $result = [];
        foreach ($query->each(500, A2pSms::getDb()) as $data) {
            $data['cost'] = (double)$data['cost'];

            if (isset($data['rate'])) {
                $data['rate'] = (double)$data['rate'];
            }

            $result[] = $data;
        }

        if ($model->is_with_general_info) {
            return $this->makeGeneralInfo($result, $query, A2pSms::getDb());
        }

        return $result;
    }

    private function makeGeneralInfo($result, Query $query, Connection $db)
    {
        $queryFrom = clone $query;
        $queryFrom->limit = null;
        $queryFrom->offset = null;

        $sumQuery = new Query();
        $sumQuery->from(['a' => clone $queryFrom]);

        $sumQuery->select([
            'sum' => new Expression('SUM(cost)::decimal(12,6)'),
            'count' => new Expression('COUNT(*)')
        ]);

        $generalInfo = $sumQuery->one($db);
        $generalInfo['sum'] = (float)$generalInfo['sum'];
        $generalInfo['offset'] = (int)$query->offset;
        $generalInfo['limit'] = (int)$query->limit;

        return [
            'info' => $generalInfo,
            'data' => $result,
        ];
    }
}
