<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\web\NotFoundHttpException;

class MvnoController extends ApiInternalController
{
    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Get(
     *   tags={"MVNO"},
     *   path="/internal/mvno/get-balance",
     *   summary="Получение баланса ЛС по номеру",
     *   operationId="Получение баланса ЛС по номеру",
     *   @SWG\Parameter(name = "msisdn", type = "string", description = "Номер телефона", in = "query", default = ""),
     *   @SWG\Response(response=200, description="Получение баланса ЛС по номеру",
     *     @SWG\Schema(type="object", required={"account_balance","subaccount_balance"},
     *       @SWG\Property(property="account_balance", type="number"),
     *       @SWG\Property(property="subaccount_balance", type="number")
     *       )
     *     )
     *   )
     * )
     */
    public function actionGetBalance()
    {
        $msisdn = isset($this->requestData['msisdn']) ? $this->requestData['msisdn'] : null;

        if (!$msisdn) {
            throw new BadRequestHttpException();
        }

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where([
            'voip_number' => $msisdn,
            'service_type_id' => ServiceType::ID_VOIP
        ])->andWhere(
            ['IS NOT', 'tariff_period_id', null]
        )->one();

        if (!$accountTariff) {
            throw new NotFoundHttpException('ЛС не найден');
        }

        $balance = $accountTariff->clientAccount->billingCounters->getRealtimeBalance();

        return [
            'account_balance' => $balance,
            'account_balance_available' => $accountTariff->clientAccount->credit + $balance,
            'subaccount_balance' => null
        ];
    }
}