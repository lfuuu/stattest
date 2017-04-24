<?php
namespace app\classes\api;

use app\classes\HttpClient;
use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class ApiPhone
 *
 * @method static ApiPhone me($args = null)
 */
class ApiPhone extends Singleton
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
        return isset(Yii::$app->params['PHONE_SERVER']) ? Yii::$app->params['PHONE_SERVER'] : '';
    }

    /**
     * @return bool
     */
    private function _getApiUrl()
    {
        $phoneHost = $this->_getHost();
        return $phoneHost ? 'https://' . $phoneHost . '/phone/api/' : '';
    }

    /**
     * @return array
     */
    private function _getApiAuthorization()
    {
        return isset(Yii::$app->params['VPBX_API_AUTHORIZATION']) ? Yii::$app->params['VPBX_API_AUTHORIZATION'] : [];
    }

    /**
     * @return array
     */
    public function getMultitranks()
    {
        $data = [];
        try {
            foreach ($this->exec('multitrunks', null) as $d) {
                $data[$d['id']] = $d['name'];
            }
        } catch (\Exception $e) {
            // так исторически сложилось
        }

        return $data;
    }

    /**
     * @param string $action
     * @param array $data
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     */
    public function exec($action, $data)
    {
        if (!$this->isAvailable()) {
            throw new InvalidConfigException('API Phone was not configured');
        }

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod('post')
            ->setData($data)
            ->setUrl($this->_getApiUrl() . $action)
            ->auth($this->_getApiAuthorization())
            ->getResponseDataWithCheck();
    }

    /**
     * @param ClientAccount $client
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     */
    public function getNumbersInfo(ClientAccount $client)
    {
        return $this->exec('numbers_info', ['account_id' => $client->id]);
    }

    /**
     * @param ClientAccount $clientAccount
     * @param int $did
     * @param \DateTimeImmutable $date
     * @return array. int_number_amount=>... или errors=>...
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public function getResourceVoipLines(ClientAccount $clientAccount, $did, \DateTimeImmutable $date)
    {
        return $this->exec(
            'get_did_client_lines',
            [
                'timezone' => $clientAccount->timezone_name,
                'date_log' => $date->format(DateTimeZoneHelper::DATE_FORMAT),
                'account_id' => $clientAccount->id,
                'did' => $did,
            ]
        );
    }

}
