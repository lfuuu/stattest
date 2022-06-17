<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\Country;
use app\models\LkNoticeSetting;
use Yii;
use yii\db\Expression;
use yii\db\Query;

class ClientContactController extends ApiInternalController
{

    /**
     * @SWG\GET(
     *   tags={"Список контактов лицевого счета"},
     *   path="/internal/client-contact/get/",
     *   summary="Получение списка контактов лицевого счета",
     *   operationId="Получение списка контактов лицевого счета",
     *   @SWG\Parameter(name="clientAccountId",type="integer",description="ID лицевого счета",in="query",required=true,default=""),
     *   @SWG\Parameter(name="eventType",type="integer",description="Тип контакта (email / phone / fax / sms / email_invoice / email_rate / email_support etc))",in="query",default=""),
     *   @SWG\Parameter(name="isOfficial",type="integer",description="Официальный контакт (вкл. = 1 / выкл. = 0), по умолчанию - все",in="query",default=""),
     *   @SWG\Parameter(name="limit",type="integer",description="Кол-во контактов, по умолчанию - все",in="query",default=""),
     *   @SWG\Response(
     *     response=200,
     *     description="результат работы метода",
     *     @SWG\Definition(
     *       type="object",
     *       @SWG\Property(property="id", type="integer", description="ID контакта"),
     *       @SWG\Property(property="client_id", type="integer", description="ID лицевого счета"),
     *       @SWG\Property(property="type", type="string", description="Тип контакта"),
     *       @SWG\Property(property="data", type="string", description="Контакт"),
     *       @SWG\Property(property="user_id", type="integer", description="ID пользователя, добавшего контакт"),
     *       @SWG\Property(property="ts", type="integer", description="Дата создания контакта"),
     *       @SWG\Property(property="comment", type="string", description="Описание контакта"),
     *       @SWG\Property(property="is_active", type="boolean", description="Активность контакта"),
     *       @SWG\Property(property="is_official", type="boolean", description="Официальность контакта"),
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
    /**
     * @param int $clientAccountId
     * @param string $eventType
     * @param int|null $isOfficial
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     * @throws \yii\base\InvalidConfigException|ModelValidationException
     */
    public function actionGet($clientAccountId, $eventType = '', $isOfficial = null, $limit = 0)
    {
        Yii::info('Called "/internal/client-contact/get/"');

        $result = (new Query)
            ->select([
                'contacts.*',
                'is_active' => new Expression('1'), // для совместимости с API
            ])
            ->from(['contacts' => ClientContact::tableName()]);

        $model = DynamicModel::validateData(
            [
                'client_id' => $clientAccountId,
                'type' => $eventType,
                'is_official' => $isOfficial,
                'limit' => $limit,
            ],
            [
                [['client_id', 'limit', 'is_official'], 'integer'],
                ['type', 'string'],
                ['client_id', 'required'],
                ['type', 'in', 'range' => array_keys(ClientContact::$types)],
            ]
        );

        if ($model->hasErrors()) {
            throw new ModelValidationException($model);
        }

        $clientAccount = ClientAccount::findOne(['id' => $model->client_id]);

        if (!$clientAccount) {
//            throw new \InvalidArgumentException('Account not found');
            return ['b' => 1];
        }

        $countryId = $clientAccount->getUuCountryId();

        if (
            \Yii::$app->isRus() && (!$countryId || $countryId == Country::RUSSIA)
            || (!\Yii::$app->isRus() && $countryId != Country::RUSSIA)
        ) {
            // OK
        } else {
//            throw new \InvalidArgumentException('not allowed');
            return [];
        }

        $result->andWhere(['contacts.client_id' => $model->client_id]);
        if (!is_null($model->is_official)) {
            $result->andWhere(['contacts.is_official' => $model->is_official]);
        }

        if ($model->type) {
            $result->andWhere(['contacts.type' => $model->type]);
        }

        if ((int)$model->limit) {
            $result->limit($model->limit);
        }

        return $result->all();
    }

    /**
     * @SWG\GET(
     *   tags={"Список контактов лицевого счета"},
     *   path="/internal/client-contact/get-personal/",
     *   summary="Получение списка персональных контактов лицевого счета",
     *   operationId="Получение списка персональных контактов лицевого счета",
     *   @SWG\Parameter(name="clientAccountId",type="integer",description="ID лицевого счета",in="query",required=true,default=""),
     *   @SWG\Parameter(name="eventType",type="integer",description="Тип контакта (email / phone / fax / sms / email_invoice / email_rate / email_support etc))",in="query",default=""),
     *   @SWG\Parameter(name="limit",type="integer",description="Кол-во контактов, по умолчанию - все",in="query",default=""),
     *   @SWG\Response(
     *     response=200,
     *     description="результат работы метода",
     *     @SWG\Definition(
     *       type="object",
     *       @SWG\Property(property="id", type="integer", description="ID контакта"),
     *       @SWG\Property(property="client_id", type="integer", description="ID лицевого счета"),
     *       @SWG\Property(property="type", type="string", description="Тип контакта"),
     *       @SWG\Property(property="data", type="string", description="Контакт"),
     *       @SWG\Property(property="user_id", type="integer", description="ID пользователя, добавшего контакт"),
     *       @SWG\Property(property="ts", type="integer", description="Дата создания контакта"),
     *       @SWG\Property(property="comment", type="string", description="Описание контакта"),
     *       @SWG\Property(property="is_active", type="boolean", description="Активность контакта"),
     *       @SWG\Property(property="is_official", type="boolean", description="Официальность контакта"),
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
    /**
     * @param $clientAccountId
     * @param string $eventType
     * @param int $limit
     * @return array
     * @throws ModelValidationException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetPersonal($clientAccountId, $eventType = '', $limit = 0)
    {
        Yii::info('Called "/internal/client-contact/get-personal/"');

        $result = (new Query)
            ->select([
                'contacts.*',
                'lk_settings.min_balance',
                'lk_settings.min_day_limit',
                'lk_settings.add_pay_notif',
            ])
            ->from(['contacts' => ClientContact::tableName()]);

        $model = DynamicModel::validateData(
            [
                'client_id' => $clientAccountId,
                'type' => $eventType,
                'limit' => $limit,
            ],
            [
                [['client_id', 'limit',], 'integer'],
                ['type', 'string'],
                ['client_id', 'required'],
                ['type', 'in', 'range' => array_keys(ClientContact::$types)],
            ]
        );

        if ($model->hasErrors()) {
            throw new ModelValidationException($model);
        }
        $clientAccount = ClientAccount::findOne(['id' => $model->client_id]);

        if (!$clientAccount) {
//            throw new \InvalidArgumentException('Account not found');
            return ['b' => 1];
        }

        $countryId = $clientAccount->getUuCountryId();

        if (
            \Yii::$app->isRus() && (!$countryId || $countryId == Country::RUSSIA)
            || (!\Yii::$app->isRus() && $countryId != Country::RUSSIA)
        ) {
            // OK
        } else {
//            throw new \InvalidArgumentException('not allowed');
            return [];
        }

        $result->andWhere([
            'contacts.client_id' => $model->client_id,
            'contacts.is_official' => 0,

        ]);

        if ($model->type) {
            $result->andWhere(['contacts.type' => $model->type]);
        }

        $result->innerJoin(
            ['lk_settings' => LkNoticeSetting::tableName()],
            'lk_settings.client_id = contacts.client_id AND lk_settings.client_contact_id = contacts.id'
        );

        if ((int)$model->limit) {
            $result->limit($model->limit);
        }

        return $result->all();
    }

}