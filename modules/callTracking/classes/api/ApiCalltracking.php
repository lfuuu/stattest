<?php

namespace app\modules\callTracking\classes\api;

use app\classes\HttpClient;
use app\classes\Singleton;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class ApiCalltracking
 *
 * @method static ApiCalltracking me($args = null)
 */
class ApiCalltracking extends Singleton
{
    /**
     * @return bool
     */
    public function isAvailable()
    {
        return (bool)$this->_getHost();
    }

    /**
     * @return string
     */
    private function _getHost()
    {
        return isset(Yii::$app->params['CALLTRACKING_SERVER']) ? Yii::$app->params['CALLTRACKING_SERVER'] : '';
    }

    /**
     * @return bool
     */
    private function _getApiUrl()
    {
        $calltrackingServer = $this->_getHost();
        return $calltrackingServer ? 'https://' . $calltrackingServer . '/daemon/api/private/api/calltracker/service/' : '';
    }


    /**
     * @param string $action
     * @param array $data
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     */
    protected function _exec($action, $data)
    {
        if (!$this->isAvailable()) {
            throw new InvalidConfigException('API Calltracking was not configured');
        }

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod('post')
            ->setData($data)
            ->setUrl($this->_getApiUrl() . $action)
            ->getResponseDataWithCheck();

    }


    /**
     * @param int $clientAccountId
     * @param int $statProductId
     * @return array
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function create(
        $clientAccountId,
        $statProductId = null
    )
    {
        return $this->_exec('create', ['account_id' => $clientAccountId, 'stat_product_id' => $statProductId]);
    }

    /**
     * @param int $clientAccountId
     * @param $statProductId
     * @return array
     * @throws InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function delete($clientAccountId, $statProductId)
    {
        $params = [
            'account_id' => $clientAccountId,
            'stat_product_id' => $statProductId,
        ];

        return $this->_exec('delete', $params);
    }
}
