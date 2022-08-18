<?php

namespace app\controllers\api;

use app\classes\api\SberbankApi;
use app\classes\media\MediaManager;
use app\exceptions\api\SberbankApiException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\Currency;
use app\models\danycom\PhoneHistory;
use app\models\EntryPoint;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\media\ClientFiles;
use app\models\Organization;
use app\models\Payment;
use app\models\SberbankOrder;
use app\models\Trouble;
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
use yii\db\Expression;

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
            Yii::$app->request->get() ?: Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::class],
            ]
        );

        $form->validateWithException();

        /** @var ClientAccount $account */
        $account = ClientAccount::find()->where(["id" => $form->account_id])->with(['clientContractModel', 'superClient'])->one();
        Assert::isObject($account);

        $contract = $account->clientContractModel;
        Assert::isObject($contract);

        $shopId = null;
        $scId = null;
        if (
            isset(\Yii::$app->params['yandex'])
            && isset(\Yii::$app->params['yandex']['kassa'][$contract->organization_id]['shop_id'])
        ) {
            $shopId = \Yii::$app->params['yandex']['kassa'][$contract->organization_id]['shop_id'];
            $scId = \Yii::$app->params['yandex']['kassa'][$contract->organization_id]['sc_id'];
        }

        $danycomData = [];
        if ($account->superClient->entry_point_id == EntryPoint::ID_MNP_RU_DANYCOM) {
            $numbers = AccountTariff::find()->where(['client_account_id' => $account->id, 'service_type_id' => ServiceType::ID_VOIP])->select('voip_number')->column();

            /** @TODO: привести номера в e164 */
            array_walk($numbers, function(&$number){
                $number = substr($number, 1);
            });

            $numberAndPosition = PhoneHistory::find()->select(['number' => new Expression('max(number)')])
                ->where(['phone_contact' => $numbers])
                ->groupBy('phone_contact')
                ->indexBy('phone_contact')
                ->column();

            if ($numberAndPosition) {
                $history = PhoneHistory::find();

                $danycomData['phones'] = $history
                    ->select(['process_id', 'region'])
                    ->addSelect([
                        'status' => 'state',
                        'date_start' => 'date_request',
                        'date_end' => 'date_ported',
                        'from_operator' => 'from',
                        'number' => new Expression('concat(\'7\', phone_ported)')
                    ])
                    ->andWhere([
                        'id' =>
                            PhoneHistory::find()
                                ->where(['phone_contact' => $numbers])
                                ->andWhere(new Expression('phone_contact = phone_ported'))
                                ->select(new Expression('max(id)'))->groupBy('phone_ported'),
                    ])
                    ->addOrderBy(['phone_ported' => SORT_ASC])
                ->asArray()
                ->all();
            }
        }

        return [
            'id' => $account->id,
            'country_id' => $account->country_id,
            'connect_point_id' => $account->region,
            'currency' => $account->currency,
            'country_lang' => $account->country->lang,
            'version' => $account->account_version,
            'yandex_shop_id' => $shopId,
            'yandex_sc_id' => $scId,
            'is_only_yandex' => $contract->organization_id == Organization::MCN_TELECOM,
            'organization_id' => $contract->organization_id,
            'legal_type' => $contract->clientContragent->legal_type,
            'price_level' => $account->price_level,
        ] + ($danycomData ? ['danycom' => $danycomData] : []);
    }


    /**
     * @SWG\Post(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/lk/account-info-st/",
     *   summary="Получение информации о лицевом счёте с состоянием ответа",
     *   operationId="Получение информации о лицевом счёте с состоянием ответа",
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
    public function actionAccountInfoSt()
    {
        try {
            return [
                'status' => 'ok',
                'result' => $this->actionAccountInfo(),
                ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
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
     * @SWG\Response(
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
                ['account_id', AccountIdValidator::class],
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
                'id' => $file->id,
                'name' => $file->name,
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
                ['account_id', AccountIdValidator::class],
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
            ImportantEvents::create(
                ImportantEventsNames::DOCUMENT_UPLOADED_LK,
                ImportantEventsSources::SOURCE_STAT, [
                'client_id' => $account->id,
                'file_id' => $file->id,
                'file_name' => $file->name
            ]);

            Trouble::dao()->createTrouble(
                $account->id,
                Trouble::TYPE_CONNECT,
                Trouble::SUBTYPE_CONNECT,
                'Клиент загрузил файл: ' . $file->name . ' ( ' . \Yii::$app->params['SITE_URL'] . 'file/get-file?model=clients&id=' . $file->id . ' )',
                null,
                $account->contract->account_manager
            );

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
     * @SWG\Post(
     *   tags={"Работа с файлами"},
     *   path="/lk/get-file/",
     *   summary="Получение файла",
     *   operationId="Получение файла",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="file_id",type="integer",description="идентификатор файла",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="Описание файла",
     *     )
     *   ),
     * )
     **/
    public function actionGetFile()
    {
        $form = DynamicModel::validateData(
            \Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::class],
                ['file_id', 'required'],
                ['file_id', 'integer'],
            ]
        );

        $form->validateWithException();

        $files = array_column($this->_getFiles($form->account_id), 'id');

        if (!in_array($form->file_id, $files)) {
            return [
                'error' => [
                    ['code' => 'File not found']
                ]
            ];
        }

        $account = ClientAccount::findOne(['id' => $form->account_id]);
        $file = ClientFiles::find()->where(['id' => $form->file_id, 'contract_id' => $account->contract_id])->one();


        /** @var ClientMedia $media */
        $media = $file->mediaManager;

        $filePath = $media->getFilePath($file);

        if (!file_exists($filePath)) {
            return [
                'error' => [
                    ['code' => 'File not found']
                ]
            ];
        }

        $fileData = $media->getFile($file);

        $content = file_get_contents($filePath);
        $fileData['content'] = base64_encode($content);
        $fileData['length'] = mb_strlen($content, '8bit');

        return [
            'id' => $fileData['id'],
            'name' => $fileData['name'],
            'mimeType' => $fileData['mimeType'],
            'content' => $fileData['content'],
            'length' => $fileData['length'],
        ];
    }

    /**
     * Создание счета на оплату и регистрация его в Сбербанк. С обоработкой состояния.
     * POST /api/lk/make-sberbank-order-st
     *
     * @return array
     */

    public function actionMakeSberbankOrderSt()
    {
        try {
            return [
                'status' => 'ok',
                'result' => $this->actionMakeSberbankOrder()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    /**
     * POST /api/lk/make-sberbank-order
     *
     * Создание счета на оплату и регистрация его в Сбербанк.
     */
    public function actionMakeSberbankOrder()
    {
        $form = DynamicModel::validateData(
            \Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::class],
                ['sum', 'double', 'min' => 10, 'max' => 15000],
                ['is_pay_page', 'boolean'],
            ]
        );

        $form->validateWithException();

        $account = ClientAccount::findOne(['id' => $form->account_id]);

        $organizationId = $account->contract->organization_id;

        // Yandex money pay only in russia, sber if else
        if ($organizationId == Organization::MCN_TELECOM && \Yii::$app->isRus()) {
            $shopId = $scId = null;
            if (
                isset(\Yii::$app->params['yandex'])
                && isset(\Yii::$app->params['yandex']['kassa'][$organizationId]['shop_id'])
            ) {
                $shopId = \Yii::$app->params['yandex']['kassa'][$organizationId]['shop_id'];
                $scId = \Yii::$app->params['yandex']['kassa'][$organizationId]['sc_id'];
            }

            if (!$shopId || !$scId) {
                throw new \Exception('Config error (c'.$organizationId.')');
            }

            return [
                'provider' => 'sber',
                'order_url' => 'https://yoomoney.ru/eshop.xml?' . http_build_query([
                        'scid' => $scId,
                        'ShopID' => $shopId,
                        'Sum' => $form->sum,
                        'CustomerNumber' => $account->id,
                        'paymentType' => 'AC',
                    ])
            ];

            /*
            return [
                'provider' => 'yandex',
                'yandex_shop_id' => $shopId,
                'yandex_sc_id' => $scId,
                'account_id' => $account->id
            ];
            */
        }

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

        $sberbankApi = new SberbankApi($bill->clientAccount->contract->organization_id);

        if ($sbOrder->status == SberbankOrder::STATUS_NOT_REGISTERED) {
            $reg = $sberbankApi->register(
                $bill->clientAccount,
                $bill->bill_no,
                $form->sum,
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
            'provider' => 'sber',
            'order_url' => $sbOrder->order_url
        ];
    }

    /**
     * Создание счета на оплату и регистрация его в Сбербанк. С обоработкой состояния.
     * POST /api/lk/make-sberbank-order-st
     *
     * @return array
     */

    public function actionApplySberbankPaymentSt()
    {
        try {
            $result = $this->actionApplySberbankPayment();

            if ($result == ['status' => 'ok']) {
                return $result;
            }
            return [
                'status' => 'ok',
                'result' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
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

        $form->validateWithException();

        $sbOrder = SberbankOrder::findOne(['order_id' => $form->order_id]);

        if (!$sbOrder || $sbOrder->status == SberbankOrder::STATUS_NOT_REGISTERED || !$sbOrder->bill || !$sbOrder->bill->clientAccount) {
            throw new \Exception('Status Sberbank order status error');
        }

        if ($sbOrder->status == SberbankOrder::STATUS_PAYED) {
            return [
                'status' => 'ok'
            ];
        }

        $sbApi = new SberbankApi($sbOrder->bill->clientAccount->contract->organization_id);
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
