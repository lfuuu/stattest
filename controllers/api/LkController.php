<?php

namespace app\controllers\api;

use Yii;
use app\classes\Assert;
use app\classes\DynamicModel;
use app\classes\ApiController;
use app\classes\validators\AccountIdValidator;
use app\exceptions\FormValidationException;
use app\models\ClientAccount;

class LkController extends ApiController
{
    public function actionAccountInfo()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams, 
            [
                ["account_id", AccountIdValidator::className()],
                [["account_id"], "required"],
            ]
        );

        if ($form->hasErrors()) 
        {
            throw new FormValidationException($form);
        }

        $account = ClientAccount::findOne(["id" => $form->account_id]);
        Assert::isObject($account);

        return [
            "id" => $account->id,
            "country_id" => $account->country_id,
            "connect_point_id" => $account->region,
            "currency" => $account->currency
            ];
    }
}
