<?php

namespace app\controllers\api\internal;

use Yii;
use app\classes\ApiInternalController;
use app\models\ClientAccount;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\api\internal\PartnerNotFoundException;

class AccountController extends ApiInternalController
{
    public function actionIndex()
    {
        $requestData = $this->getRequestParams();

        $accountId = isset($requestData['account_id']) ? $requestData['account_id'] : null;

        if (!$accountId) {
            throw new BadRequestHttpException;
        }

        if ($accountId && ($account = ClientAccount::findOne(['id' => $accountId]))) {

            $data = [
                'id' => $account->id, 
                'usages' => ['501' => 'Not Implemented']
            ];

            return $data;
        } else {
            throw new BadRequestHttpException;
        }
    }
}
