<?php

namespace app\controllers\api\internal;

use Yii;
use app\classes\ApiInternalController;
use app\models\ClientAccount;
use app\models\ActualNumber;
use app\models\ActualVirtpbx;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\api\internal\PartnerNotFoundException;

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
}
