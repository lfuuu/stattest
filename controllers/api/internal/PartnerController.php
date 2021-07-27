<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\models\ClientAccount;
use app\dao\PartnerDao;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use app\exceptions\api\internal\PartnerNotFoundException;

class PartnerController extends ApiInternalController
{
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с клиентами"},
     *   path="/internal/partner/clients/",
     *   summary="Получение клиентов партнёра",
     *   operationId="Получение клиентов партнёра",
     *   @SWG\Parameter(name="partner_id",type="integer",description="идентификатор партнёра",in="formData"),
     *   @SWG\Parameter(name="is_full_client_info",type="integer",description="Расширенная информация о клиентах",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="данные о клиентах партнёра",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/client"
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
    public function actionClients()
    {
        $partnerId = isset($this->requestData['partner_id']) ? $this->requestData['partner_id'] : null;
        $isFullClientInfo = isset($this->requestData['is_full_client_info']) ? $this->requestData['is_full_client_info'] : null;

        if (!$partnerId || !($account = ClientAccount::findOne(['id' => $partnerId]))) {
            throw new BadRequestHttpException;
        }

        if ($account->isPartner()) {
            return PartnerDao::getClientsStructure($account, $isFullClientInfo);
        } else {
            throw new PartnerNotFoundException;
        }
    }
}
