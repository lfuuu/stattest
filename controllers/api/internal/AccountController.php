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
    public function actionIndex()
    {
        $requestData = $this->getRequestParams();

        $accountId = isset($requestData['account_id']) ? $requestData['account_id'] : null;

        if (!$accountId) {
            throw new BadRequestHttpException;
        }

        if ($accountId && ($account = ClientAccount::findOne(['id' => $accountId]))) {

            $activeVoips = [];
            foreach(ActualNumber::findAll(['client_id' => $account->id]) as $v) {
                $activeVoips[$v->id] = [
                    'stat_product_id' => $v->id,
                    'number' => $v->number,
                    'region' => $v->region
                ];
            }

            $activeVats = [];
            foreach(ActualVirtpbx::findAll(['client_id' => $account->id]) as $v) {
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
        } else {
            throw new BadRequestHttpException;
        }
    }
}
