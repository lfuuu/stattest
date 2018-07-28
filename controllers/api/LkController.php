<?php

namespace app\controllers\api;

use app\classes\api\SberbankApi;
use app\exceptions\api\SberbankApiException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\Currency;
use app\models\media\ClientFiles;
use app\models\Payment;
use app\models\SberbankOrder;
use app\models\UsageVoip;
use app\models\User;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use Yii;
use app\classes\Assert;
use app\classes\DynamicModel;
use app\classes\ApiController;
use app\classes\validators\AccountIdValidator;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use yii\base\InvalidParamException;

/**
 * Class LkController
 */
class LkController extends ApiController
{
    const MAX_UPLOAD_FILES = 10;
    /**
     * @SWG\Post(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/lk/account-info/",
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
            throw new ModelValidationException($form);
        }

        $account = ClientAccount::findOne(["id" => $form->account_id]);
        Assert::isObject($account);

        return [
            'id' => $account->id,
            'country_id' => $account->country_id,
            'connect_point_id' => $account->region,
            'currency' => $account->currency,
            'country_lang' => $account->country->lang,
            'version' => $account->account_version,
        ];
    }

    /**
     * @SWG\Definition(
     *   definition="file_list",
     *   type="object",
     *   required={"name"},
     *   @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Название файла"
     *   ),
     * ),
     * @SWG\Post(
     *   tags={"Работа с файлами"},
     *   path="/lk/get-files/",
     *   summary="Получение списка файлов",
     *   operationId="Получение списка файлов",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="Список файлов",
     *       @SWG\Items(
     *         ref="#/definitions/file_list"
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
     **/
    public function actionGetFiles()
    {
        $accountId = (int)(isset(Yii::$app->request->bodyParams['account_id']) ?
            Yii::$app->request->bodyParams['account_id'] :
            0
        );

        $form = DynamicModel::validateData(
            ['account_id' => $accountId],
            [
                ['account_id', AccountIdValidator::className()],
            ]
        );

        if ($form->hasErrors()) {
            throw new ModelValidationException($form);
        }

        return $this->_getFiles($form->account_id);
    }

    /**
     * Возвращает список файлов, прикрепленных к договору ЛС
     *
     * @param int $accountId id ЛС
     * @return array
     */
    private function _getFiles($accountId)
    {
        $account = ClientAccount::findOne(["id" => $accountId]);
        Assert::isObject($account);

        $files = [];
        foreach (ClientFiles::findAll([
            "contract_id" => $account->contract_id,
            "is_show_in_lk" => 1
        ]) as $file) {
            $files[] = [
                "name" => $file->name
            ];
        }

        return $files;
    }


    /**
     * @SWG\Definition(
     *   definition="file",
     *   type="object",
     *   required={"name","content"},
     *   @SWG\Property(property="name",type="string",description="название файла"),
     *   @SWG\Property(property="content",type="string",description="содержимое файла"),
     * ),
     * @SWG\Post(
     *   tags={"Работа с файлами"},
     *   path="/lk/save-file/",
     *   summary="Сохранение документа",
     *   operationId="Сохранение документа",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="file",type="file",description="файл",in="formData",@SWG\Schema(ref="#/definitions/file")),
     *   @SWG\Response(
     *     response=200,
     *     description="Загруженный файл",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="file_name",type="string",description="название файла"),
     *       @SWG\Property(property="file_id",type="integer",description="идентификатор файла"),
     *       @SWG\Property(property="is_can_upload",type="integer",description="Возмодно ли ещё загрузить файл"),
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
     **/
    public function actionSaveFile()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::className()],
            ]
        );

        if ($form->hasErrors()) {
            throw new ModelValidationException($form);
        }

        /** @var ClientAccount $account */
        $account = ClientAccount::findOne(["id" => $form->account_id]);
        Assert::isObject($account);

        $files = $this->_getFiles($account->id);

        if (count($files) >= self::MAX_UPLOAD_FILES) {
            return ["errors" => [["code" => "max upload file limit"]]];
        }

        $data = Yii::$app->request->bodyParams;

        if (!isset($data["file"]) || !isset($data["file"]["name"]) || !$data["file"]["content"]) {
            throw new InvalidParamException("data_error");
        }

        $file = $account->contract->mediaManager->addFileFromParam(
            $name = $data["file"]["name"],
            $content = base64_decode($data["file"]["content"]),
            $comment = "ЛК - wizard",
            $userId = User::CLIENT_USER_ID,
            $isShowInLk = true
        );

        if ($file) {
            return [
                "file_name" => $file->name,
                "file_id" => $file->id,
                "is_can_upload" => (count($files) + 1 < self::MAX_UPLOAD_FILES)
            ];
        } else {
            return [
                "errors" => [
                    [
                        "code" => "error upload file"
                    ]
                ]
            ];
        }
    }

    /**
     * Создание счета на оплату и регистрация его в Сбербанк.
     */
    public function actionMakeSberbankOrder()
    {
        $form = DynamicModel::validateData(
            \Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::className()],
                ['sum', 'double', 'min' => 10, 'max' => 15000],
                ['is_pay_page', 'boolean'],
            ]
        );

        $form->validateWithException();

        $bill = Bill::dao()->getPrepayedBillOnSum($form->account_id, $form->sum, Currency::RUB, $isForceCreate = true);

        $sbOrder = SberbankOrder::findOne(['bill_no' => $bill->bill_no]);

        if ($sbOrder && $sbOrder->status == SberbankOrder::STATUS_PAYED) {
            throw new \Exception('Bill already payed');
        }

        if (!$sbOrder) {
            $sbOrder = new SberbankOrder();
            $sbOrder->bill_no = $bill->bill_no;
            $sbOrder->status = SberbankOrder::STATUS_NOT_REGISTERED;
            $sbOrder->save();
            $sbOrder->refresh();
        }

        $sberbankApi = new SberbankApi();

        if ($sbOrder->status == SberbankOrder::STATUS_NOT_REGISTERED) {
            $reg = $sberbankApi->register(
                $form->account_id,
                $bill->bill_no,
                $form->sum,
                $bill->clientAccount->currency,
                $form->is_pay_page
            );

            $sbOrder->status = SberbankOrder::STATUS_REGISTERED;
            $sbOrder->order_id = $reg['orderId'];
            $sbOrder->order_url = $reg['formUrl'];
            $sbOrder->save();
        }

        $info = $sberbankApi->getOrderStatusExtended($sbOrder->order_id);
        $sbOrder->info_json = json_encode($info, JSON_UNESCAPED_UNICODE);
        $sbOrder->save();

        return [
            'order_url' => $sbOrder->order_url
        ];
    }

    /**
     * Внесение платежа, прошедшего через Сбербанк
     *
     * @throws ModelValidationException
     */
    public function actionApplySberbankPayment()
    {

        $form = DynamicModel::validateData(
            \Yii::$app->request->bodyParams,
            [
                ['order_id', 'required']
            ]
        );

        if ($form->hasErrors()) {
            throw new ModelValidationException($form);
        }

        $sbOrder = SberbankOrder::findOne(['order_id' => $form->order_id]);

        if (!$sbOrder || $sbOrder->status == SberbankOrder::STATUS_NOT_REGISTERED) {
            throw new \Exception('Status Sberbank order status error');
        }

        if ($sbOrder->status == SberbankOrder::STATUS_PAYED) {
            return [
                'status' => 'ok'
            ];
        }

        $sbApi = new SberbankApi;
        $info = $sbApi->getOrderStatusExtended($sbOrder->order_id);

        if ($info['orderStatus'] != SberbankApi::ERROR_PAYED) {
            throw new \Exception($info['errorMessage']);
        }

        $sbOrder->makePayment($info);

        ClientAccount::dao()->updateBalance($sbOrder->bill->client_id);

        return [
            'status' => 'ok'
        ];
    }

    public function actionGetAccountByPhone()
    {
        $form = DynamicModel::validateData(
            \Yii::$app->request->bodyParams,
            [
                ['phone', 'required']
            ]
        );

        /** @var UsageVoip $usage */
        $usage = UsageVoip::find()->actual()->phone($form->phone)->one();

        if ($usage) {
            return ['account_id' => $usage->clientAccount->id];
        }

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()->where([
            'voip_number' => $form->phone,
            'service_type_id' => ServiceType::ID_VOIP
        ])
            ->andWhere(['NOT', ['tariff_period_id' => null]])
            ->one();

        if ($accountTariff) {
            return ['account_id' => $accountTariff->client_account_id];
        }

        return false;
    }
}
