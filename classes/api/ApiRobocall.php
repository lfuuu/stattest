<?php

namespace app\classes\api;

use app\classes\Assert;
use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\classes\HttpClient;
use app\models\ClientAccount;
use app\models\EventQueue;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class ApiRobocall
 */
class ApiRobocall extends Singleton
{
    const EVENT_ADD_TR_CONTACT = 'robocall_add_transaction_contact';

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return isset(Yii::$app->params['ROBOCALL_AUTH'])
            && isset(Yii::$app->params['API_SERVER'])
            && Yii::$app->params['ROBOCALL_AUTH']
            && Yii::$app->params['API_SERVER'];
    }

    /**
     * @return bool
     */
    public function isAvailableDefaultParams()
    {
        return isset(Yii::$app->params['ROBOCALL_DEFAULT_PARAMS'])
            && Yii::$app->params['ROBOCALL_DEFAULT_PARAMS']
            && isset(Yii::$app->params['ROBOCALL_DEFAULT_PARAMS']['account_id'])
            && Yii::$app->params['ROBOCALL_DEFAULT_PARAMS']['account_id']
            && isset(Yii::$app->params['ROBOCALL_DEFAULT_PARAMS']['task_id'])
            && Yii::$app->params['ROBOCALL_DEFAULT_PARAMS']['task_id']
            && isset(Yii::$app->params['ROBOCALL_DEFAULT_PARAMS']['robocall_id'])
            && Yii::$app->params['ROBOCALL_DEFAULT_PARAMS']['robocall_id'];
    }

    /**
     * @return bool|string
     */
    public function getApiUrl()
    {
        return $this->isAvailable() ? Yii::$app->params['API_SERVER'] : false;
    }

    /**
     * @return array
     */
    public function getApiAuthorization()
    {
        return isset(Yii::$app->params['ROBOCALL_AUTH']) ? Yii::$app->params['ROBOCALL_AUTH'] : false;
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
    private function exec($action, $data, $isPostJSON = true)
    {
        if (!$this->isAvailable()) {
            throw new InvalidConfigException('API Robocall was not configured');
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
     * @param int $taskId
     * @param int $accountId
     * @param int $robocallId
     * @param string $phone
     * @param array $userVariables
     * @param \DateTimeImmutable|null $dateStart
     * @param \DateTimeImmutable|null $dateStop
     * @return mixed
     */
    public function addTransactionContact($taskId, $accountId, $robocallId, $phone, $userVariables = [], \DateTimeImmutable $dateStart = null, \DateTimeImmutable $dateStop = null)
    {
        $dateStart = $dateStart ?: (new \DateTimeImmutable('now'))->setTime(0, 0, 0);
        $dateStop = $dateStop ?: $dateStart->modify('+1 day');

        return $this->exec('/account/' . $accountId . '/robocall/' . $robocallId . '/add_transaction_contact', [
            'task_id' => $taskId,
            'phone' => preg_replace('/[^\d]+/', '', $phone),
            'options' => json_encode([
                    'timezone' => 'GMT+03',
                    'start_at' => $dateStart->format(DateTimeZoneHelper::DATETIME_FORMAT),
                    'stop_at' => $dateStop->format(DateTimeZoneHelper::DATETIME_FORMAT),
                ] + ($userVariables ? ['user_variables' => $userVariables] : [])),
        ]);
    }

    /**
     * @param int $accountId
     * @return string
     * @throws InvalidConfigException
     */
    public function addTaskByBlockAccount($accountId)
    {
        if (!$this->isAvailable() || !$this->isAvailableDefaultParams()) {
            throw new InvalidConfigException('API Robocall was not configured');
        }

        $account = ClientAccount::findOne(['id' => $accountId]);
        Assert::isObject($account);

        $defaultParams = Yii::$app->params['ROBOCALL_DEFAULT_PARAMS'];

        $phone = '';

        if ($account->contract->accountManagerUser) {
            $phone = $account->contract->accountManagerUser->phone_mobile;
        }

        if (!$phone) {
            return 'no phone';
        }

        EventQueue::go(self::EVENT_ADD_TR_CONTACT, $defaultParams + [
                'phone' => $phone,
                'user_variables' => [
                    'balance' => $account->billingCounters->realtimeBalance,
                    'name' => $account->contragent->name,
                ]
            ]);
    }
}
