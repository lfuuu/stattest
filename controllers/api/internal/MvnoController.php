<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use app\models\ClientSubAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\web\NotFoundHttpException;

class MvnoController extends ApiInternalController
{
    private $_msisdn = null;

    /** @var AccountTariff */
    private $_accountTariff = null;
    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Get(tags = {"MVNO"}, path = "/internal/mvno/get-balance", summary = "Получение баланса ЛС по номеру", operationId = "mvno_get_balance",
     *   @SWG\Parameter(name = "msisdn", type = "string", description = "Номер телефона", in = "query", default = "", required = true),
     *   @SWG\Response(response = 200, description = "Получение баланса ЛС по номеру",
     *     @SWG\Schema(type = "object", required = {"account_balance", "account_balance_available", "subaccount_balance"},
     *       @SWG\Property(property = "account_balance", type = "number"),
     *       @SWG\Property(property = "account_balance_available", type = "number"),
     *       @SWG\Property(property = "subaccount_balance", type = "number")
     *       )
     *     )
     *   )
     * )
     */
    public function actionGetBalance()
    {
        $this->_foundAndGetAccountTariff();

        $balance = $this->_accountTariff->clientAccount->billingCounters->getRealtimeBalance();

        return [
            'account_balance' => $balance,
            'account_balance_available' => $this->_accountTariff->clientAccount->credit + $balance,
            'subaccount_balance' => null
        ];
    }

    /**
     * @SWG\Get(tags = {"MVNO"}, path = "/internal/mvno/packages-remain", summary = "Возвращает название подключенного пакета, остатки по пакету, дата окончания действия пакета, если задана", operationId = "mvno_packages_remain",
     *   @SWG\Parameter(name = "msisdn", type = "string", description = "Номер телефона", in = "query", default = "", required = true),
     *   @SWG\Response(response = 200, description = "название подключенного пакета, остатки и сроки действия",
     *     @SWG\Schema(type = "object", required = {"package_name", "package_remain", "package_end_date"},
     *       @SWG\Property(property = "package_name", type = "string"),
     *       @SWG\Property(property = "package_remain", type = "string"),
     *       @SWG\Property(property = "package_end_date", type = "string")
     *       )
     *     )
     *   )
     * )
     */
    /**
     * Заглушка.
     *
     * @return array
     */
    public function actionPackagesRemain()
    {
        $this->_foundAndGetAccountTariff();

        return [
            'package_name' => 'packages #' . $this->_accountTariff->id,
            'package_remain' => '2017-10-01',
            'package_end_date' => null
        ];

    }

    /**
     * @SWG\Get(tags = {"MVNO"}, path = "/internal/mvno/can-send-sms", summary = "Проверка возможности отправки СМС", operationId = "mvno_can_send_sms",
     *   @SWG\Parameter(name = "msisdn", type = "string", description = "Номер телефона", in = "query", default = "", required = true),
     *   @SWG\Parameter(name = "messagesCount", type = "number", description = "Кол-во СМС", in = "query", default = "", required = true),
     *   @SWG\Response(response = 200, description = "Возможность отправки СМС",
     *       @SWG\Property(type = "boolean")
     *     )
     *   )
     * )
     */
    /**
     * Заглушка.
     *
     * @return bool
     * @throws BadRequestHttpException
     */
    public function actionCanSendSms()
    {
        $this->_foundAndGetAccountTariff();

        $messagesCount = isset($this->requestData['messagesCount']) ? (int)$this->requestData['messagesCount'] : 0;

        if ($messagesCount <= 0) {
            throw new BadRequestHttpException('invalide messagesCount');
        }

        return $messagesCount < 5; // заглушка
    }

    /**
     * @SWG\Get(tags = {"MVNO"}, path = "/internal/mvno/get-my-number", summary = "Запрос данных о номере", operationId = "mvno_get_my_number",
     *   @SWG\Parameter(name = "msisdn", type = "string", description = "Номер телефона", in = "query", default = "", required = true),
     *   @SWG\Response(response = 200, description = "Данные о номере",
     *     @SWG\Schema(type = "object", required = {"msisdn","account_id","sub_account_id"},
     *       @SWG\Property(property = "msisdn", type = "number"),
     *       @SWG\Property(property = "account_id", type = "number"),
     *       @SWG\Property(property = "sub_account_id", type = "number")
     *       )
     *     )
     *   )
     * )
     */
    /**
     * @return array
     */
    public function actionGetMyNumber()
    {
        $this->_foundAndGetAccountTariff();

        $subAccount = $this->_getSubAccount(false);

        return [
            'msisdn' => $this->_msisdn,
            'account_id' => $this->_accountTariff->client_account_id,
            'sub_account_id' => $subAccount ? $subAccount->sub_account : null
        ];
    }

    /**
     * @SWG\Get(tags = {"MVNO"}, path = "/internal/mvno/get-my-limits", summary = "Запрос лимитов по субсчету", operationId = "mvno_get_my_limits",
     *   @SWG\Parameter(name = "msisdn", type = "string", description = "Лимиты по субсчету", in = "query", default = "", required = true),
     *   @SWG\Response(response = 200, description = "Лимиты по субсчету",
     *     @SWG\Schema(type = "object", required = {"msisdn","account_id","sub_account_id"},
     *       @SWG\Property(property = "msisdn", type = "number"),
     *       @SWG\Property(property = "account_id", type = "number"),
     *       @SWG\Property(property = "sub_account_id", type = "number")
     *     )
     *   )
     * )
     */
    /**
     * @return array
     */
    public function actionGetMyLimits()
    {
        $this->_foundAndGetAccountTariff();

        $subAccount = $this->_getSubAccount();

        return [
            'day_limit' => $subAccount->voip_limit_day,
            'month_limit' => $subAccount->voip_limit_month,
            'day_mn_limit' => $subAccount->voip_limit_mn_day,
            'month_mb_limit' => $subAccount->voip_limit_mn_month,
            'is_blocked' => $subAccount->is_voip_blocked,
            'is_orig_disabled' => $subAccount->is_voip_orig_disabled,
        ];
    }



    /**
     * @return AccountTariff
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    private function _foundAndGetAccountTariff()
    {
        $this->_msisdn = isset($this->requestData['msisdn']) ? $this->requestData['msisdn'] : null;

        if (!$this->_msisdn) {
            throw new BadRequestHttpException();
        }

        /** @var AccountTariff $accountTariff */
        $this->_accountTariff = AccountTariff::find()->where([
            'voip_number' => $this->_msisdn,
            'service_type_id' => ServiceType::ID_VOIP
        ])->andWhere(
            ['IS NOT', 'tariff_period_id', null]
        )->one();

        if (!$this->_accountTariff) {
            throw new NotFoundHttpException('ЛС не найден');
        }
    }

    /**
     * Получение субаккаунта по ЛС и DID
     *
     * @param bool $isWithException
     * @return ClientSubAccount
     * @throws NotFoundHttpException
     */
    private function _getSubAccount($isWithException = true)
    {
        $subAccount = ClientSubAccount::findOne([
            'account_id' => $this->_accountTariff->client_account_id,
            'did' => $this->_msisdn,
        ]);

        if ($isWithException && !$subAccount) {
            throw new NotFoundHttpException('СубАккаунт не найден');
        }

        return $subAccount;
    }

}