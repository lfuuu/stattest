<?php

namespace app\controllers\api\internal;

use app\classes\DynamicModel;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\FormFieldValidator;
use app\exceptions\ModelValidationException;
use app\classes\ApiInternalController;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\SubaccountCounter;
use app\models\ClientSubAccount;
use yii\base\InvalidParamException;

class SubAccountController extends ApiInternalController
{
    /**
     * @SWG\Definition(definition = "subaccount", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "идентификатор записи"),
     *   @SWG\Property(property = "account_id", type = "integer", description = "идентификатор лицевого счёта"),
     *   @SWG\Property(property = "sub_account", type = "string", description = "ID субаккаунта ВАТС"),
     *   @SWG\Property(property = "stat_product_id", type = "integer", description = "ID услуги стата"),
     *   @SWG\Property(property = "number", type = "integer", description = "Внутренний номер"),
     *   @SWG\Property(property = "did", type = "number", description = "DID-номер"),
     *   @SWG\Property(property = "balance", type = "number", description = "Баланс"),
     *   @SWG\Property(property = "credit", type = "integer", description = "Кредитный лимит"),
     *   @SWG\Property(property = "amount_date", type = "string", description = "Дата последнего выставления счета"),
     *   @SWG\Property(property = "voip_limit_month", type = "integer", description = "Месячный лимит"),
     *   @SWG\Property(property = "voip_limit_day", type = "integer", description = "Дневной лимит"),
     *   @SWG\Property(property = "voip_limit_mn_day", type = "integer", description = "Дневной лимит на международку"),
     *   @SWG\Property(property = "voip_limit_mn_month", type = "integer", description = "Месячный лимит на международку"),
     *   @SWG\Property(property = "is_voip_orig_disabled", type = "boolean", description = "Оригинация отключена"),
     *   @SWG\Property(property = "is_voip_blocked", type = "boolean", description = "Телефония заблокирована"),
     *   @SWG\Property(property = "amount_month_sum", type = "number", description = "Расходы с начала месяца"),
     *   @SWG\Property(property = "amount_day_sum", type = "number", description = "Расходы с начала суток")
     * ),
     * @SWG\Get(tags={"SubAccount"}, path = "/internal/sub-account/", summary = "Получение списка субаккаунтов",
     *   operationId = "Получение списка субаккаунтов",
     *   @SWG\Parameter(name = "id", type = "integer", description = "идентификатор записи", default = "", in = "query"),
     *   @SWG\Parameter(name = "account_id", type = "integer", description = "идентификатор лицевого счёта", default = "", in = "query"),
     *   @SWG\Parameter(name = "stat_product_id", type = "integer", description = "ID услуги стата", default = "", in = "query"),
     *   @SWG\Parameter(name = "sub_account", type = "string", description = "ID субаккаунта ВАТС", default = "", in = "query"),
     *   @SWG\Parameter(name = "number", type = "integer", description = "Внутренний номер", default = "", in = "query"),
     *   @SWG\Parameter(name = "did", type = "number", description = "DID-номер", default = "", in = "query"),
     *   @SWG\Parameter(name = "credit", type = "integer", description = "Кредитный лимит", default = "", in = "query"),
     *   @SWG\Parameter(name = "amount_date", type = "string", description = "Дата последнего выставления счета", default = "", in = "query"),
     *   @SWG\Parameter(name = "voip_limit_month", type = "integer", description = "Месячный лимит", default = "", in = "query"),
     *   @SWG\Parameter(name = "voip_limit_day", type = "integer", description = "Дневной лимит", default = "", in = "query"),
     *   @SWG\Parameter(name = "voip_limit_mn_day", type = "integer", description = "Дневной лимит на международку", default = "", in = "query"),
     *   @SWG\Parameter(name = "voip_limit_mn_month", type = "integer", description = "Месячный лимит на международку", default = "", in = "query"),
     *   @SWG\Parameter(name = "is_voip_orig_disabled", type = "integer", description = "Оригинация отключена", @SWG\Items(type = "integer"), enum={"","0","1"}, default = "", in = "query"),
     *   @SWG\Parameter(name = "is_voip_blocked", type = "integer", description = "Телефония заблокирована", @SWG\Items(type = "integer"), enum={"","0","1"}, default = "", in = "query"),
     *   @SWG\Response(response=200, description = "Список субаккаунтов",
     *     @SWG\Schema(type = "array",
     *       @SWG\Items(ref = "#/definitions/subaccount"
     *       )
     *     )
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionIndex()
    {
        $model = new ClientSubAccount();

        // валидация
        if ($this->requestData && (!$model->load($this->requestData, '') || !$model->validate())) {
            throw new ModelValidationException($model);
        }

        // получение данных
        $data = ClientSubAccount::find()
            ->where(['is_enabled' => true])
            ->andFilterWhere($model->getAttributes())
            ->asArray()
            ->all();

        // получение балансов
        $subAccountSums = SubaccountCounter::find()
            ->where([
                'subaccount_id' => array_column($data, 'id'),
                'client_account_id' => array_unique(array_column($data, 'account_id')),
            ])
            ->indexBy('subaccount_id')
            ->asArray()
            ->all();

        foreach ($data as &$row) {
            $row['balance'] = 0;
            $row['amount_month_sum'] = 0;
            $row['amount_day_sum'] = 0;

            if (isset($subAccountSums[$row['id']])) {
                $subAccountSum = $subAccountSums[$row['id']];

                $row['balance'] = (float) $subAccountSum['amount_sum'];
                $row['amount_month_sum'] = (float) $subAccountSum['amount_month_sum'];
                $row['amount_day_sum'] = (float) $subAccountSum['amount_day_sum'];
            }
            unset($row['is_enabled']);
        }

        return $data;
    }

    /**
     * @SWG\Definition(definition = "batchSubAccountLevel2", type = "object",
     *     @SWG\Property(property = "id", type = "integer", description = "ID записи"),
     *     @SWG\Property(property = "sub_account", type = "integer", description = "ID субаккаунта ВАТС"),
     *     @SWG\Property(property = "is_sub_account_enable", type = "boolean", description = "Субаккаунт включен"),
     *     @SWG\Property(property = "number", type = "integer", description = "Внутренний номер"),
     *     @SWG\Property(property = "did", type = "string", description = "Номер прикрепленного мобильного телефона"),
     *     @SWG\Property(property = "name", type = "string", description = "Описание номера", default = ""),
     *     @SWG\Property(property = "balance", type = "number", description = "Баланс субаккаунта"),
     *     @SWG\Property(property = "credit", type = "number", description = "Кредитный лимит"),
     *     @SWG\Property(property = "amount_date", type = "string", description = "Дата последнего выставления счета"),
     *     @SWG\Property(property = "voip_limit_month", type = "number", description = "Месячный лимит", default = 0),
     *     @SWG\Property(property = "voip_limit_day", type = "number", description = "Дневной лимит", default = 0),
     *     @SWG\Property(property = "voip_limit_mn_day", type = "number", description = "Дневной лимит на международку", default = 0),
     *     @SWG\Property(property = "voip_limit_mn_month", type = "number", description = "Месячный лимит на международку", default = 0),
     *     @SWG\Property(property = "is_voip_orig_disabled", type = "boolean", default = false, description = "Заблокированны исходящие звонки"),
     *     @SWG\Property(property = "is_voip_blocked", type = "boolean", default = false, description = "Телефония заблокированна"),
     *     @SWG\Property(property = "is_voip_limit_month_enabled", type = "boolean", default = false, description = "Установлен месячный лимит на телефонию"),
     *     @SWG\Property(property = "is_voip_limit_mn_month_enabled", type = "boolean", default = false, description = "Установлен месячный лимит на МГМН "),
     *     @SWG\Property(property = "is_voip_limit_day_enabled", type = "boolean", default = false, description = "Установлен дневной лимит на телефонию"),
     *     @SWG\Property(property = "is_voip_limit_mn_day_enabled", type = "boolean", default = false, description = "Установлен лневной димит на МГМН"),
     * ),
     * @SWG\Definition(definition = "batchSubAccountLevel1", type = "object", required = {"account_id", "stat_product_id"},
     *     @SWG\Property(property = "account_id", type = "integer", description = "ID лицевого счета"),
     *     @SWG\Property(property = "stat_product_id", type = "integer", description = "ID услуги стат"),
     *     @SWG\Property(property = "sub_accounts", type = "array", @SWG\Items(ref = "#/definitions/batchSubAccountLevel2"), description = "Список субаккаунтов"),
     * ),
     * @SWG\Post(tags={"SubAccount"}, path = "/internal/sub-account/batch-update", summary = "Пакетное обновление субакаунтов", operationId = "SubAccountBatchUpdate",
     *   @SWG\Parameter(name = "", type = "object", @SWG\Schema(ref="#/definitions/batchSubAccountLevel1"), in = "body"),
     *   @SWG\Response(response = 200, description = "СубАккаунт удален",
     *     @SWG\Schema(type = "boolean", description = "Результат удаления")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    public function actionBatchUpdate()
    {
        $data = \Yii::$app->request->bodyParams;

        // validate level 1
        $model = DynamicModel::validateData(
            $data,
            [
                ['account_id', AccountIdValidator::class],
                ['stat_product_id', 'integer', 'min' => 0],
                ['sub_accounts', 'required']
            ]
        );

        $dataLevel1 = [
            'account_id' => $data['account_id'],
            'stat_product_id' => $data['stat_product_id']
        ];

        $flatData = [];

        if ($model->hasErrors()) {
            throw new ModelValidationException($model);
        }

        if (!is_array($data['sub_accounts'])) {
            throw new InvalidParamException('sub_accounts is not array');
        }

        // validate level 2
        foreach ($data['sub_accounts'] as $subAccountRow) {
            $model = DynamicModel::validateData(
                $subAccountRow,
                [
                    [['id', 'sub_account', 'is_sub_account_enable', 'number'], 'required'],
                    [['id', 'sub_account'], 'integer', 'min' => 1],
                    ['is_sub_account_enable', 'boolean'],
                    ['name', FormFieldValidator::class],
                    [
                        [
                            'number',
                            'voip_limit_month',
                            'voip_limit_day',
                            'voip_limit_mn_day',
                            'voip_limit_mn_month',
                            'balance',
                            'credit'
                        ],
                        'number'
                    ],
                    [['is_voip_orig_disabled', 'is_voip_blocked', 'is_voip_limit_mn_month_enabled', 'is_voip_limit_month_enabled', 'is_voip_limit_day_enabled', 'is_voip_limit_mn_day_enabled'], 'boolean'],
                    ['amount_date', 'datetime', 'format' => 'php:' . DateTimeZoneHelper::DATETIME_FORMAT]
                ]
            );

            if ($model->hasErrors()) {
                throw new ModelValidationException($model);
            }

            $flatData[] = $dataLevel1 + $subAccountRow;
        }


        // do actions
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($flatData as $row) {
                $model = new ClientSubAccount();

                $model->setScenario('save');

                // валидация
                if (!$model->load($row, '') || !$model->validate()) {
                    throw new ModelValidationException($model);
                }

                // получаем заполненные атрибуты. Функция нужна на случай появления "левых" данных.
                $attributes = array_filter(
                    $model->getAttributes(),
                    function ($value) {
                        return $value !== null;
                    }
                );

                $attributes['is_enabled'] = (int)(bool)$row['is_sub_account_enable'];

                /** @var ClientSubAccount $subAccount */
                $subAccount = ClientSubAccount::findOne(['id' => $model->id]);

                if (!$subAccount) {
                    if (!$attributes['is_enabled']) {
                        continue;
                    }
                    $subAccount = new ClientSubAccount();
                }

                $subAccount->setAttributes($attributes);

                if (!$subAccount->save()) {
                    throw new ModelValidationException($subAccount);
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }
}
