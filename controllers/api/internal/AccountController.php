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

    public function actionIndex()
    {
        $account = $this->getAccountFromParams();

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
    }

    public function actionBalance()
    {
        $account = $this->getAccountFromParams();

        return $account->makeBalance();
    }

    public function actionBalanceFull()
    {
        $account = $this->getAccountFromParams();

        return $account->makeBalance(true);
    }
}
