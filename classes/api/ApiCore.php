<?php

namespace app\classes\api;

use app\classes\Assert;
use app\classes\HttpRequest;
use app\models\EventQueue;
use app\classes\HttpClient;
use app\exceptions\ModelValidationException;
use app\models\Business;
use app\models\ClientContact;
use app\models\ClientSuper;
use app\models\CoreSyncIds;
use app\models\EventQueueIndicator;
use app\models\important_events\ImportantEventsNames;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;

/**
 * Class ApiCore
 */
class ApiCore
{
    const ERROR_PRODUCT_NOT_EXSISTS = 538; // "Приложения 'vpbx' для лицевого счёта '####' не существует";

    /**
     * @return bool
     */
    public static function isAvailable()
    {
        return isset(Yii::$app->params['CORE_SERVER']) && Yii::$app->params['CORE_SERVER'];
    }

    /**
     * @return bool|string
     */
    public static function getApiUrl()
    {
        return self::isAvailable() ? 'https://' . Yii::$app->params['CORE_SERVER'] . '/core/api/' : false;
    }

    /**
     * @return array
     */
    public static function getApiAuthorization()
    {
        return isset(Yii::$app->params['VPBX_API_AUTHORIZATION']) ? Yii::$app->params['VPBX_API_AUTHORIZATION'] : [];
    }

    /**
     * @param string $action
     * @param array $data
     * @param bool $isPostJSON
     * @return mixed
     * @throws InvalidConfigException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\web\BadRequestHttpException
     */
    public static function exec($action, $data, $isPostJSON = true)
    {
        if (!self::isAvailable()) {
            throw new InvalidConfigException('API Core was not configured');
        }

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod($isPostJSON ? 'post' : 'get')
            ->setData($data)
            ->setUrl(self::getApiUrl() . $action)
            ->auth(self::getApiAuthorization())
            ->getResponseDataWithCheck();
    }

    /**
     * @param int $contragentId
     * @param int $fromClientId
     * @param int $toClientId
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\web\BadRequestHttpException
     */
    public static function transferContragent($contragentId, $fromClientId, $toClientId)
    {
        self::exec(ImportantEventsNames::TRANSFER_CONTRAGENT, [
            'from_client_id' => $fromClientId,
            'to_client_id' => $toClientId,
            'contragent_id' => $contragentId,
        ]);
    }

    /**
     * Вызов API функции. Заведен ли емайл как главный в каком-либо клиенте ЛК
     *
     * @param string $email
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public static function isEmailExists($email)
    {
        // для тестов
        if (defined("YII_ENV") && YII_ENV == "test") {
            return false;
        }

        $result = self::exec('is_email_exists', ['email' => $email]);

        if (isset($result['exists'])) {
            return $result['exists'];
        }

        throw new \Exception('[core/is_email_exists] Ответ не найден!');
    }

    /**
     * Вызов API функции. Создан ли ЛК для клиента
     *
     * @param int $clientSuperId
     * @return boolean
     * @throws \Exception
     */
    public static function isLkExists($clientSuperId = 0)
    {
        $result = self::exec('client/' . $clientSuperId . '/lk_exists', null, false);

        if (isset($result['exists'])) {
            return $result['exists'];
        }

        throw new \Exception('[core/client/is_lk_exists] Ответ не найден!');
    }

    /**
     * Проверка необходимости создания админа на платформе
     *
     * @param int $superId
     * @throws ModelValidationException
     */
    public static function checkCreateCoreAdmin($superId)
    {
        EventQueueIndicator::deleteAll([
            'object' => ClientSuper::tableName(),
            'object_id' => $superId,
        ]);

        if (!self::isAvailable()) {
            return;
        }

        $super = ClientSuper::findOne(['id' => $superId]);
        Assert::isObject($super);

        $account = $super->getFirstAccount();
        Assert::isObject($account);

        // заказ Интернет-магазина
        if ($account->contract->business_id == Business::INTERNET_SHOP) {
            return;
        }

        if (CoreSyncIds::findOne([
            "id" => $account->super_id,
            "type" => CoreSyncIds::TYPE_SUPER_CLIENT])
        ) {
            // Уже синхронизированно
            return;
        }

        /** @var ClientContact $adminEmail */
        $adminEmail = $account
            ->getContacts()
            ->andWhere([
                'type' => ClientContact::TYPE_EMAIL,
                'is_official' => 1
            ])
            ->one();

        /** @var ClientContact $adminEmail */
        $phone = $account
            ->getContacts()
            ->andWhere([
                'type' => ClientContact::TYPE_PHONE,
            ])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->select('data')
            ->scalar();

        if (!$adminEmail) {
            return;
        }

        EventQueue::goWithIndicator(
            EventQueue::CORE_CREATE_OWNER,
            [
                'id' => $superId,
                'account_id' => $account->id,
                'email' => $adminEmail->data,
                'name' => $account->contragent->name,
                'contract_id' => $account->contract_id,
            ] + ($phone ? ['phone' => str_replace('+', '', $phone)] : []),
            \app\models\ClientSuper::tableName(),
            $superId,
            null,
            '+15 seconds'
        );
    }

    /**
     * Создание админа на платформе
     *
     * @param array $params
     * @return string
     * @throws ModelValidationException
     */
    public static function syncCoreOwner($params)
    {
        $accountSync = new CoreSyncIds;
        $accountSync->id = $params['id'];
        $accountSync->type = CoreSyncIds::TYPE_SUPER_CLIENT;

//        $info = self::createCoreOwner($params['id'], $params['account_id'], $params['email'], isset($params['phone']) && $params['phone'] ? $params['phone'] : null );
        $info = ApiBase::me()->userCreateCoreOwner($params['email'], $params['name'], $params['contract_id']);

        $accountSync->external_id = $info['result'];

        if (!$accountSync->save()) {
            throw new ModelValidationException($accountSync);
        }

        return 'Создан администратор ЛК с emailом: ' . $params['email'];
    }

    /**
     * Вызов API функции. Создание админа в ЛК
     *
     * @param int $superClientId
     * @param int $accountId
     * @param string $email
     * @param string $phone
     * @return array
     */
    public static function createCoreOwner($superClientId, $accountId, $email, $phone = null)
    {
        $result = ApiCore::exec('create_core_owner', [
                'id' => $superClientId,
                'email' => $email,
                'account_id' => $accountId,
            ] + ($phone ? ['phone' => $phone] : []));

        if (isset($result['data'])) {
            return $result['data'];
        }

        // @TODO событие "Пользователь с таким email существует" (код 503) должно обрабатываться в функции вызова. Надо внести правки на платформу

        list ($code, $msg) = HttpRequest::recognizeAnError($result);

        if ($code) {
            throw new InvalidCallException($msg, $code);
        }

        throw new InvalidCallException('[core/create_core_owner] Непонятный ответ платформы: ' . var_export($result, true));
    }
}
