<?php

namespace app\controllers\api\internal;

use Yii;
use app\classes\ApiInternalController;
use app\models\ClientAccount;
use app\models\Business;
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

    public function actionClients()
    {
        $partnerId = isset($this->requestData['partner_id']) ? $this->requestData['partner_id'] : null;

        if (!$partnerId || !($account = ClientAccount::findOne(['id' => $partnerId]))) {
            throw new BadRequestHttpException;
        }

        if ($account->isPartner()) {
            return PartnerDao::getClientsStructure($account);
        } else {
            throw new PartnerNotFoundException;
        }
    }
}
