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
    /**
     * @SWG\Post(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/lk/account_info/",
     *   summary="Получение информации о лицевом счёте",
     *   operationId="Получение информации о лицевом счёте",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="информация о лицевом счёте",
     *     @SWG\Definition(
     *       type="object",
     *       required={"id","country_id","connect_point_id","currency"},
     *       @SWG\Property(property="id",type="integer",description="идентификатор лицевого счёта"),
     *       @SWG\Property(property="country_id",type="integer",description="идентификатор страны"),
     *       @SWG\Property(property="connect_point_id",type="integer",description="идентификатор точки подключения"),
     *       @SWG\Property(property="currency",type="integer",description="валюта")
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
    public function actionAccountInfo()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::className()],
            ]
        );

        if ($form->hasErrors()) {
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
