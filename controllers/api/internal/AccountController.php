<?php

namespace app\controllers\api\internal;

use Yii;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\api\internal\ExceptionValidationAccountId;
use app\exceptions\api\internal\PartnerNotFoundException;
use app\classes\Assert;
use app\classes\ApiInternalController;
use app\models\ClientAccount;
use app\models\ActualNumber;
use app\models\ActualVirtpbx;
use app\models\Business;
use app\models\ClientContract;
use app\models\Region;

class AccountController extends ApiInternalController
{
    private function getAccountFromParams()
    {
        $accountId = isset($this->requestData['account_id']) ? $this->requestData['account_id'] : null;

        if (!$accountId) {
            throw new BadRequestHttpException;
        }

        if ($accountId && ($account = ClientAccount::findOne(['id' => $accountId]))) {
            return $account;
        } else {
            throw new BadRequestHttpException;
        }
    }

    /**
     * @SWG\Definition(
     *   definition="voip_account_service",
     *   type="object",
     *   required={"stat_product_id","number","region"},
     *   @SWG\Property(
     *     property="stat_product_id",
     *     type="integer",
     *     description="Идентификатор услуги"
     *   ),
     *   @SWG\Property(
     *     property="number",
     *     type="integer",
     *     description="Телефонный номер"
     *   ),
     *   @SWG\Property(
     *     property="region",
     *     type="integer",
     *     description="Регион"
     *   )
     * ),
     * @SWG\Definition(
     *   definition="vats_account_service",
     *   type="object",
     *   required={"stat_product_id","number","region"},
     *   @SWG\Property(
     *     property="stat_product_id",
     *     type="integer",
     *     description="Идентификатор услуги"
     *   ),
     *   @SWG\Property(
     *     property="region",
     *     type="integer",
     *     description="Регион"
     *   )
     * ),
     * @SWG\Post(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/internal/account/",
     *   summary="Получение списка услуг по лицевому счёту",
     *   operationId="Получение списка услуг по лицевому счёту",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData",required=true),
     *   @SWG\Response(
     *     response=200,
     *     description="данные об услугах",
     *     @SWG\Schema(
     *       type="object",
     *       required={"id","usages"},
     *       @SWG\Property(
     *         property="id",
     *         type="integer",
     *         description="Идентификатор лицевого счёта"
     *       ),
     *       @SWG\Property(
     *         property="usages",
     *         type="object",
     *         description="Услуги",
     *         @SWG\Property(
     *           property="active",
     *           type="object",
     *           description="Сгруппированные услуги",
     *           @SWG\Property(
     *             property="voip",
     *             type="array",
     *             description="Услуги телефонии",
     *             @SWG\Items(
     *               ref="#/definitions/voip_account_service"
     *             )
     *           ),
     *           @SWG\Property(
     *             property="vats",
     *             type="array",
     *             description="Услуги ВАТС",
     *             @SWG\Items(
     *               ref="#/definitions/vats_account_service"
     *             )
     *           )
     *         ),
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
    public function actionIndex()
    {
        $account = $this->getAccountFromParams();

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
     * @SWG\Post(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/internal/account/balance/",
     *   summary="Получение баланса лицевого счёта",
     *   operationId="Получение баланса лицевого счёта",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData",required=true),
     *   @SWG\Response(
     *     response=200,
     *     description="данные об услугах",
     *     @SWG\Schema(
     *       type="object",
     *       required={"balance","currency"},
     *       @SWG\Property(
     *         property="balance",
     *         type="number",
     *         description="Баланс"
     *       ),
     *       @SWG\Property(
     *         property="currency",
     *         type="string",
     *         description="Валюта"
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
    public function actionBalance()
    {
        $account = $this->getAccountFromParams();

        return $account->makeBalance();
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/internal/account/balance-full/",
     *   summary="Получение полного баланса лицевого счёта",
     *   operationId="Получение полного баланса лицевого счёта",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData",required=true),
     *   @SWG\Response(
     *     response=200,
     *     description="данные об услугах",
     *     @SWG\Schema(
     *       type="object",
     *       required={"id","balance","currency","credit","expenditure","view_mode"},
     *       @SWG\Property(
     *         property="id",
     *         type="integer",
     *         description="Идентификатор лицевого счёта"
     *       ),
     *       @SWG\Property(
     *         property="balance",
     *         type="number",
     *         description="Баланс"
     *       ),
     *       @SWG\Property(
     *         property="currency",
     *         type="string",
     *         description="Валюта"
     *       ),
     *       @SWG\Property(
     *         property="credit",
     *         type="number",
     *         description="Кредитный лимит"
     *       ),
     *       @SWG\Property(
     *         property="expenditure",
     *         type="string",
     *         description="Дополнительные данные"
     *       ),
     *       @SWG\Property(
     *         property="view_mode",
     *         type="string",
     *         description="Режим отображения"
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
    public function actionBalanceFull()
    {
        $account = $this->getAccountFromParams();

        return $account->makeBalance(true);
    }

    /**
     * @SWG\Get(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/internal/account/end-of-the-day-accounts/",
     *   summary="Получение списка лицевых счетов у которых заканчиваются сутки",
     *   operationId="Получение списка лицевых счетов у которых заканчиваются сутки",
     *   @SWG\Response(response=200, description="Список таймзон, и включенных лицевых счетов",
     *     @SWG\Schema(type="object", required={"timezones","account_ids"},
     *       @SWG\Property(property="timezones",   type="array", @SWG\Items(type="string")),
     *       @SWG\Property(property="account_ids", type="array", @SWG\Items( type="integer")
     *       )
     *     )
     *   )
     * )
     */
    public function actionEndOfTheDayAccounts()
    {
        $timeZones = [];

        foreach (Region::getTimezoneList() as $timeZone) {

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
                function($a) {
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
     * @SWG\Get(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/internal/account/set-partner-login-allow",
     *   summary="Установка/Снятие флага-разрешения доступа к ЛК для парнера-родиителя",
     *   operationId="Установка/Снятие флага-разрешения доступа к ЛК для парнера-родиителя",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="query"),
     *   @SWG\Parameter(name="value",type="integer",description="значение флага (Да/Нет)",in="query"),
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
     * @param int $accountId
     * @param int $value
     * @return array
     * @throws ExceptionValidationAccountId
     * @throws PartnerNotFoundException
     */
    public function actionSetPartnerLoginAllow($accountId, $value)
    {
        $clientAccount = ClientAccount::findOne(['id' => $accountId]);
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
            throw new PartnerNotFoundException('Partner for client not found');
        }

        $clientContract->partner_login_allow = $value ? 1 : 0;
        if (!$clientContract->save()) {
            return [
                'error' => 'Cant save contract',
            ];
        }

        return ['message' => 'success'];
    }

}
