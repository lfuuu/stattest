<?php
namespace app\classes\api;

use app\classes\JSONQuery;
use app\models\important_events\ImportantEventsNames;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 * Class ApiCore
 * @package app\classes\api
 */
class ApiCore
{
    const ERROR_PRODUCT_NOT_EXSISTS = 538;//"Приложения 'vpbx' для лицевого счёта '####' не существует";

    public static function isAvailable()
    {
        return isset(\Yii::$app->params['CORE_SERVER']) && \Yii::$app->params['CORE_SERVER'];
    }

    public static function getApiUrl()
    {
        return self::isAvailable() ? 'https://' . \Yii::$app->params['CORE_SERVER'] . '/core/api/' : false;
    }

    public static function exec($action, $data, $isPostJSON = true)
    {
        if (!self::isAvailable()) {
            throw new InvalidConfigException('API Core was not configured');
        }

        return JSONQuery::exec(self::getApiUrl() . $action, $data, $isPostJSON);
    }

    public static function addProduct($product, $clientId, $productId = 0)
    {
        $newState = ["mnemonic" => $product];

        if ($productId) {
            $newState["stat_product_id"] = $productId;
        }

        ApiCore::exec('add_products_from_stat', \SyncCoreHelper::getAddProductStruct($clientId, $newState));
    }

    public static function remoteProduct($product, $clientId, $productId = 0)
    {
        $state = \SyncCoreHelper::getRemoveProductStruct($clientId, $product);

        if ($productId) {
            $state["stat_product_id"] = $productId;
        }

        ApiCore::exec('remove_product', $state);
    }

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
