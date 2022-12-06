<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\Assert;
use app\classes\DynamicModel;
use app\exceptions\api\internal\ExceptionValidationAccountId;
use app\exceptions\api\internal\PartnerNotFoundException;
use app\exceptions\web\BadRequestHttpException;
use app\models\ActualNumber;
use app\models\ActualVirtpbx;
use app\models\billing\StatsAccount;
use app\models\Business;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientContract;
use app\models\Timezone;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;

class AccountController extends ApiInternalController
{
    /**
     * @return ClientAccount
     * @throws BadRequestHttpException
     */
    private function _getAccountFromParams()
    {
        $accountId = isset($this->requestData['account_id']) ? $this->requestData['account_id'] : null;

        if (!$accountId) {
            throw new BadRequestHttpException;
        }

        if ($accountId && ($account = ClientAccount::findOne(['id' => $accountId]))) {
            return $account;
        }

        throw new BadRequestHttpException;
    }

    /**
     * @SWG\Definition(definition = "voip_account_service", type = "object", required = {"stat_product_id", "number", "region"},
     *   @SWG\Property(property = "stat_product_id", type = "integer", description = "Идентификатор услуги"),
     *   @SWG\Property(property = "number", type = "integer", description = "Телефонный номер"),
     *   @SWG\Property(property = "region", type = "integer", description = "Регион")
     * ),
     *
     * @SWG\Definition(definition = "vats_account_service", type = "object", required = {"stat_product_id", "number", "region"},
     *   @SWG\Property(property = "stat_product_id", type = "integer", description = "Идентификатор услуги"),
     *   @SWG\Property(property = "region", type = "integer", description = "Регион")
     * ),
     *
     * @SWG\Post(tags = {"ClientAccount"}, path = "/internal/account/", summary = "Получение списка услуг по лицевому счёту", operationId = "Получение списка услуг по лицевому счёту",
     *   @SWG\Parameter(name = "account_id", type = "integer", description = "ID ЛС", in = "formData", default = "", required = true),
     *   @SWG\Response(response = 200, description = "данные об услугах",
     *     @SWG\Schema(type = "object", required = {"id", "usages"},
     *       @SWG\Property(property = "id", type = "integer", description = "ID ЛС"),
     *       @SWG\Property(property = "usages", type = "object", description = "Услуги",
     *         @SWG\Property(property = "active", type = "object", description = "Сгруппированные услуги",
     *           @SWG\Property(property = "voip", type = "array", description = "Услуги телефонии",
     *             @SWG\Items(ref = "#/definitions/voip_account_service")
     *           ),
     *           @SWG\Property(property = "vats", type = "array", description = "Услуги ВАТС",
     *             @SWG\Items(ref = "#/definitions/vats_account_service")
     *           )
     *         ),
     *       )
     *     )
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        $account = $this->_getAccountFromParams();

        $activeVoips = [];
        foreach (ActualNumber::findAll(['client_id' => $account->id]) as $v) {
            $activeVoips[$v->id] = [
                'stat_product_id' => $v->id,
                'number' => $v->number,
                'region' => $v->region
            ];
        }

        $activeVats = [];
        foreach (ActualVirtpbx::findAll(['client_id' => $account->id]) as $v) {
            $activeVats[$v->usage_id] = [
                'stat_product_id' => $v->usage_id,
                'region' => $v->region_id
            ];
        }

        $data = [
            'id' => $account->id,
            'usages' => [
                'active' => [
                    'voip' => $activeVoips,
                    'vats' => $activeVats
                ]
            ]
        ];

        return $data;
    }

    /**
     * @SWG\Post(tags = {"ClientAccount"}, path = "/internal/account/balance/", summary = "Получение баланса лицевого счёта", operationId = "Получение баланса лицевого счёта",
     *   @SWG\Parameter(name = "account_id", type = "integer", description = "ID ЛС", in = "formData", default = "", required = true),
     *   @SWG\Response(response = 200, description = "данные об услугах",
     *     @SWG\Schema(type = "object", required = {"balance", "currency"},
     *       @SWG\Property(property = "balance", type = "number", description = "Баланс"),
     *       @SWG\Property(property = "currency", type = "string", description = "Валюта")
     *     )
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @throws BadRequestHttpException
     */
    public function actionBalance()
    {
        $account = $this->_getAccountFromParams();

        return $account->makeBalance();
    }

    /**
     * @SWG\Post(tags = {"ClientAccount"}, path = "/internal/account/balance-full/", summary = "Получение полного баланса лицевого счёта", operationId = "Получение полного баланса лицевого счёта",
     *   @SWG\Parameter(name = "account_id", type = "integer", description = "ID ЛС", in = "formData", default = "", required = true),
     *   @SWG\Response(response = 200, description = "данные об услугах",
     *     @SWG\Schema(type = "object", required = {"id", "balance", "currency", "credit", "expenditure", "view_mode"},
     *       @SWG\Property(property = "id", type = "integer", description = "ID ЛС"),
     *       @SWG\Property(property = "balance", type = "number", description = "Баланс"),
     *       @SWG\Property(property = "currency", type = "string", description = "Валюта"),
     *       @SWG\Property(property = "credit", type = "number", description = "Кредитный лимит"),
     *       @SWG\Property(property = "expenditure", type = "string", description = "Дополнительные данные"),
     *       @SWG\Property(property = "view_mode", type = "string", description = "Режим отображения")
     *     )
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @throws BadRequestHttpException
     */
    public function actionBalanceFull()
    {
        $account = $this->_getAccountFromParams();

        return $account->makeBalance(true);
    }

    /**
     * @SWG\Get(tags = {"ClientAccount"}, path = "/internal/account/end-of-the-day-accounts/", summary = "Получение списка лицевых счетов у которых заканчиваются сутки", operationId = "Получение списка лицевых счетов у которых заканчиваются сутки",
     *   @SWG\Response(response = 200, description = "Список таймзон, и включенных лицевых счетов",
     *     @SWG\Schema(type = "object", required = {"timezones", "account_ids"},
     *       @SWG\Property(property = "timezones",  type = "array", @SWG\Items(type = "string")),
     *       @SWG\Property(property = "account_ids", type = "array", @SWG\Items(type = "integer"))
     *     )
     *   )
     * )
     */
    public function actionEndOfTheDayAccounts()
    {
        $timeZones = [];

        foreach (Timezone::getList() as $timeZone => $devNull) {

            try {
                $clientDate = new \DateTime('now', new \DateTimeZone($timeZone));
            } catch (\Exception $e) {
                continue;
            }

            if ($clientDate->format('H') != '23') {
                continue;
            }

            $timeZones[] = $timeZone;
        }

        return [
            'timezones' => $timeZones,
            'account_ids' => array_map(
                function ($a) {
                    return (int)$a;
                },
                ClientAccount::find()
                    ->where(['timezone_name' => $timeZones])
                    ->active()
                    ->select(['id'])
                    ->column()
            )
        ];
    }

    /**
     * @SWG\Get(tags = {"ClientAccount"}, path = "/internal/account/set-partner-login-allow", summary = "Установка/Снятие флага-разрешения доступа к ЛК для парнера-родиителя", operationId = "Установка/Снятие флага-разрешения доступа к ЛК для парнера-родиителя",
     *   @SWG\Parameter(name = "account_id", type = "integer", description = "ID ЛС", in = "query", default = ""),
     *   @SWG\Parameter(name = "value", type = "integer", description = "значение флага (Да/Нет)", in = "query", default = ""),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $account_id
     * @param int $value
     * @return array
     * @throws ExceptionValidationAccountId
     * @throws PartnerNotFoundException
     * @throws \yii\base\Exception
     */
    public function actionSetPartnerLoginAllow($account_id, $value)
    {
        $clientAccount = ClientAccount::findOne(['id' => $account_id]);
        if (!$clientAccount) {
            throw new ExceptionValidationAccountId;
        }

        $clientContract = ClientContract::findOne(['id' => $clientAccount->contract->id]);
        Assert::isObject($clientContract);

        if ($clientContract->business_id !== Business::TELEKOM) {
            return [
                'error' => 'Invalid client business process, only "Telekom" can grant access',
            ];
        }

        if (!$clientContract->isPartnerAgent()) {
            throw new PartnerNotFoundException();
        }

        $clientContract->is_partner_login_allow = $value ? 1 : 0;
        if (!$clientContract->save()) {
            return [
                'error' => 'Cant save contract',
            ];
        }

        return ['message' => 'success'];
    }

    /**
     * @SWG\Definition(definition = "usedSecondsRecord", type = "object",
     *   @SWG\Property(property = "account_tariff_id", type = "integer", description = "ID базовой услуги телефонии (номера, а не пакета!)"),
     *   @SWG\Property(property = "name", type = "string", description = "Название пакета"),
     *   @SWG\Property(property = "used_seconds", type = "integer", description = "Потрачено секунд"),
     *   @SWG\Property(property = "total_seconds", type = "integer", description = "Всего секунд в этом пакете"),
     * ),
     *
     * @SWG\Get(tags = {"ClientAccount"}, path = "/internal/account/get-counters", summary = "Вернуть счетчики", operationId = "GetCounters",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", default = "", required = true),
     *   @SWG\Parameter(name = "account_tariff_id", type = "integer", description = "ID базовой услуги телефонии (номера, а не пакета!)", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Счетчики",
     *     @SWG\Schema(type = "object", required = {"timezones", "account_ids"},
     *       @SWG\Property(property = "account_package_id", type = "integer", description = "ID услуги (пакета)"),
     *       @SWG\Property(property = "sum_day", type = "number", description = "Трата за сутки"),
     *       @SWG\Property(property = "sum_month", type = "number", description = "Трата за месяц"),
     *       @SWG\Property(property = "sum_mn_day", type = "number", description = "Трата МН за сутки"),
     *       @SWG\Property(property = "sum_mn_month", type = "number", description = "Трата МН за месяц"),
     *       @SWG\Property(property = "sum_mg_day", type = "number", description = "Трата МГ за сутки"),
     *       @SWG\Property(property = "sum_mg_month", type = "number", description = "Трата МГ за месяц"),
     *       @SWG\Property(property = "used_seconds", type = "array", description = "Пакеты минут", @SWG\Items(ref = "#/definitions/usedSecondsRecord")),
     *     )
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $client_account_id
     * @param int $account_tariff_id
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionGetCounters($client_account_id, $account_tariff_id = null)
    {
        // счетчики потраченных денег
        $statsAccount = StatsAccount::find()
            ->select([
                'sum_day' => 'SUM(-sum_day)',
                'sum_month' => 'SUM(-sum_month)',
                'sum_mn_day' => 'SUM(-sum_mn_day)',
            ])
            ->where(['account_id' => $client_account_id])
            ->asArray()
            ->one();

        // счетчики потраченных минут
        $statsNnpPackageMinute = StatsAccount::getStatsNnpPackageMinute($client_account_id, $account_tariff_id, false);

        return [
            'sum_day' => (float)$statsAccount['sum_day'],
            'sum_month' => (float)$statsAccount['sum_month'],
            'sum_mn_day' => (float)$statsAccount['sum_mn_day'],
            'sum_mn_month' => null,
            'sum_mg_day' => null,
            'sum_mg_month' => null,
            'used_seconds' => $statsNnpPackageMinute,
        ];
    }

    /**
     * @SWG\GET(
     *   tags={"ClientAccount"},
     *   path="/internal/account/find-by-phone-number/",
     *   summary="Поиск ЛС по номеру телефона",
     *   operationId="Поиск ЛС по номеру телефона",
     *   @SWG\Parameter(name="number",type="string",description="Номер телефона",in="query",required=true,default=""),
     *   @SWG\Response(
     *     response=200,
     *     description="результат работы метода",
     *     @SWG\Definition(
     *       type="object",
     *       @SWG\Property(property="is_found", type="boolean", description="Найдено?"),
     *       @SWG\Property(property="is_from_contact", type="boolean", description="Найдено в контактах? (иначе в услугах)"),
     *       @SWG\Property(property="account_ids", type="array", description="IDs ЛС"),
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
     * Поиск ЛС по номеру телефона
     */
    public function actionFindByPhoneNumber($number)
    {

        $model = DynamicModel::validateData(
            [
                'number' => $number,
            ],
            [
                ['number', 'required'],
            ]
        );

        if ($model->hasErrors()) {
            $errors = $model->getFirstErrors();
            throw new BadRequestHttpException(reset($errors));
        }

        [, $numbers] = ClientContact::dao()->getE164($number);

        if (!$numbers) {
            return [
                'is_found' => false
            ];
        }

        $number = str_replace('+', '', $numbers[0]);

        $clientContactAccountIds = ClientContact::find()
            ->joinWith('client.clientContractModel cc', true, 'INNER JOIN')
            ->where([
                'type' => ClientContact::$phoneTypes,
                'data' => '+' . $number,
            ])
            ->andWhere(['NOT', ['cc.business_process_status_id' => ClientContract::getOffBpsIds()]])
            ->orderBy(['client_id' => SORT_DESC])
            ->select('client_id')
            ->distinct()
            ->column();

        if ($clientContactAccountIds) {
            return [
                'is_found' => true,
                'is_from_contact' => true,
                'account_ids' => $clientContactAccountIds
            ];
        }

        $clientContactAccountIds = UsageVoip::find()
            ->joinWith('clientAccount c', true, 'INNER JOIN')
            ->phone($number)
            ->actual()
            ->select('c.id')
            ->distinct()
            ->column();

        if ($clientContactAccountIds) {
            return [
                'is_found' => true,
                'is_from_contact' => false,
                'account_ids' => $clientContactAccountIds
            ];
        }


        /** @var AccountTariff $accountTariff */
        $clientContactAccountIds = AccountTariff::find()
            ->andWhere(['voip_number' => $number])
            ->andWhere(['IS NOT', 'tariff_period_id', null])
            ->select('client_account_id')
            ->distinct()
            ->column();

        if ($clientContactAccountIds) {
            return [
                'is_found' => true,
                'is_from_contact' => false,
                'account_ids' => $clientContactAccountIds
            ];
        }

        return [
            'is_found' => false
        ];
    }
}
