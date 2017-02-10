<?php
namespace app\classes\api;

use app\classes\HttpClient;
use app\models\important_events\ImportantEventsNames;
use Yii;
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
     * @param string $product
     * @param int $clientId
     * @param int $productId
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\web\BadRequestHttpException
     */
    public static function addProduct($product, $clientId, $productId = 0)
    {
        $newState = ["mnemonic" => $product];

        if ($productId) {
            $newState["stat_product_id"] = $productId;
        }

        ApiCore::exec('add_products_from_stat', \SyncCoreHelper::getAddProductStruct($clientId, $newState));
    }

    /**
     * @param string $product
     * @param int $clientId
     * @param int $productId
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public static function remoteProduct($product, $clientId, $productId = 0)
    {
        $state = \SyncCoreHelper::getRemoveProductStruct($clientId, $product);

        if ($productId) {
            $state["stat_product_id"] = $productId;
        }

        ApiCore::exec('remove_product', $state);
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
        self::exec(ImportantEventsNames::IMPORTANT_EVENT_TRANSFER_CONTRAGENT, [
            'from_client_id' => $fromClientId,
            'to_client_id' => $toClientId,
            'contragent_id' => $contragentId,
        ]);
    }

    /**
     * Проверяем, заведен ли емайл как главный в каком-либо клиенте ЛК
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
        $result = self::exec('is_email_exists', ['email' => $email]);

        if (isset($result['exists'])) {
            return $result['exists'];
        }

        throw new \Exception('[core/is_email_exists] Ответ не найден!');
    }

    /**
     * Проверяем, создан ли ЛК для клиента
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
}
