<?php

namespace app\modules\mtt;

use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\mtt\classes\MttAdapter;
use app\modules\uu\models\AccountTariff;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
{
    // ставится в очередь в receiverCallback
    const EVENT_PREFIX = 'MttCallback_';
    const EVENT_CALLBACK_GET_ACCOUNT_BALANCE = self::EVENT_PREFIX . 'getAccountBalance';
    const EVENT_CALLBACK_GET_ACCOUNT_DATA = self::EVENT_PREFIX . 'getAccountData';
    const EVENT_CALLBACK_BALANCE_ADJUSTMENT = self::EVENT_PREFIX . 'balanceAdjustment';

    const EVENT_CLEAR_BALANCE = 'mtt_clear_balance';
    const EVENT_ADD_INTERNET = 'mtt_add_internet';
    const EVENT_CLEAR_INTERNET = 'mtt_clear_internet';

    // Цена 1 мегабайта, руб.
    // Юзер покупает пакет мегабайт, а на счет МТТ зачисляем деньги. Причем фикс по этому курсу, а не маркетинговую цену пакета.
    const MEGABYTE_COST = 0.2;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\mtt\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\mtt\commands';
        }

        // подключить конфиги
        $params = require __DIR__ . '/config/params.php';

        $localConfigFileName = __DIR__ . '/config/params.local.php';
        if (file_exists($localConfigFileName)) {
            $params = ArrayHelper::merge($params, require $localConfigFileName);
        }

        Yii::configure($this, $params);
    }

    /**
     * Подключить пакет интернет-трафика
     *
     * @param int $packageAccountTariffId ID услуги пакета
     * @param int $internetTraffic Мб
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidParamException
     * @throws \LogicException
     */
    public static function addInternetPackage($packageAccountTariffId, $internetTraffic)
    {
        $packageAccountTariff = AccountTariff::findOne(['id' => $packageAccountTariffId]);
        if (!$packageAccountTariff) {
            throw new InvalidParamException('Неправильный ID услуги');
        }

        $prevAccountTariff = $packageAccountTariff->prevAccountTariff;
        if (!$prevAccountTariff) {
            throw new InvalidParamException('Не найдена основная услуга пакета интернета');
        }

        if (!$prevAccountTariff->voip_number || Number::isMcnLine($prevAccountTariff->voip_number)) {
            throw new InvalidParamException('У основной услуги пакета интернета не указан номер телефона');
        }

        if (!$prevAccountTariff->mtt_number) {
            // MTT ID юзера неизвестен. Надо сначала его узнать
            $info = MttAdapter::me()->getAccountData($prevAccountTariff->voip_number, $prevAccountTariff->id);
            throw new \LogicException('Это не ошибка, а такой бизнес-процесс. Ожидаем асинхронный ответ от МТТ, потом продолжим. ' . $info);
        }

        // все хорошо, MTT ID юзера известен
        $message = [
            'requestId' => $prevAccountTariff->id,
            'method' => 'balanceAdjustment',
            'parameters' => [
                'name' => $prevAccountTariff->mtt_number,
                'amount' => round($internetTraffic * self::MEGABYTE_COST, 2),
                'comment' => 'Package ' . $packageAccountTariffId,
            ],
        ];
        MttAdapter::me()->publishMessage($message);

        return print_r($message, true);
    }

    /**
     * Сбросить баланс
     *
     * @param int $prevAccountTariffId ID услуги пакета
     * @throws \yii\base\InvalidParamException
     * @throws ModelValidationException
     */
    public static function clearBalance($prevAccountTariffId)
    {
        $prevAccountTariff = AccountTariff::findOne(['id' => $prevAccountTariffId]);
        if (!$prevAccountTariff) {
            throw new InvalidParamException('Неправильный ID услуги');
        }

        $prevAccountTariff->mtt_balance = null;
        if (!$prevAccountTariff->save()) {
            throw new ModelValidationException($prevAccountTariff);
        }
    }

    /**
     * Сжечь интернет-трафик по всем пакетам
     *
     * @param int $prevAccountTariffId ID услуги пакета
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidParamException
     * @throws \LogicException
     */
    public static function clearInternet($prevAccountTariffId)
    {
        $prevAccountTariff = AccountTariff::findOne(['id' => $prevAccountTariffId]);
        if (!$prevAccountTariff) {
            throw new InvalidParamException('Неправильный ID услуги');
        }

        if (!$prevAccountTariff->voip_number || Number::isMcnLine($prevAccountTariff->voip_number)) {
            throw new InvalidParamException('У основной услуги пакета интернета не указан номер телефона');
        }

        if (!$prevAccountTariff->mtt_number) {
            // MTT ID юзера неизвестен. Надо сначала его узнать
            $info = MttAdapter::me()->getAccountData($prevAccountTariff->voip_number, $prevAccountTariff->id);
            throw new \LogicException('Это не ошибка, а такой бизнес-процесс. Ожидаем асинхронный ответ от МТТ, потом продолжим. ' . $info);
        }

        if ($prevAccountTariff->mtt_balance === null) {
            // MTT баланс юзера неизвестен. Надо сначала его узнать
            // 1. не путайте, когда он известен и равен 0!
            // 2. перед постановкой clearInternetPackage в очередь надо сбросить баланс!
            $info = MttAdapter::me()->getAccountBalance($prevAccountTariff->voip_number, $prevAccountTariff->id);
            throw new \LogicException('Это не ошибка, а такой бизнес-процесс. Ожидаем асинхронный ответ от МТТ, потом продолжим. ' . $info);
        }

        if ($prevAccountTariff->mtt_balance >= -0.1 && $prevAccountTariff->mtt_balance <= 0) {
            // и так все сброшено
            return 'Balance: ' . $prevAccountTariff->mtt_balance;
        }

        // все хорошо, MTT ID юзера и его баланс известны
        $message = [
            'requestId' => $prevAccountTariff->id,
            'method' => 'balanceAdjustment',
            'parameters' => [
                'name' => $prevAccountTariff->mtt_number,
                'amount' => -round($prevAccountTariff->mtt_balance, 2), // скорректировать на отрицательную сумму
                'overdraft' => true, // можно немного загнать в минус, если за время после обновления баланса он что-то уже потратил
                'comment' => 'Clear',
            ],
        ];
        MttAdapter::me()->publishMessage($message);

        return print_r($message, true);
    }

    /**
     * Callback обработчик API-запроса getAccountBalance
     *
     * @param array $params [requestId => 100123, currency => RUB, balance => 999.99]
     * @throws \yii\base\InvalidParamException
     * @throws \app\exceptions\ModelValidationException
     */
    public static function getAccountBalanceCallback($params)
    {
        $accountTariff = AccountTariff::findOne(['id' => $params['requestId']]);
        if (!$accountTariff) {
            throw new InvalidParamException('Неправильный ID услуги: ' . print_r($params, true));
        }

        if (!isset($params['balance'])) {
            throw new InvalidParamException('Неправильный баланс MTT: ' . print_r($params, true));
        }

        $accountTariff->mtt_balance = $params['balance'];
        if (!$accountTariff->save()) {
            throw new ModelValidationException($accountTariff);
        }
    }

    /**
     * Callback обработчик API-запроса getAccountData
     *
     * @param array $params [requestId => 100123, data => [
     *      i_product => 19179,
     *      activation_date => 2017-08-24,
     *      iso_639_1 => ru,
     *      iso_4217 => RUB,
     *      i_account => 105277438,
     *      blocked => N,
     *      h323_password => t95ac5d12354403,
     *      i_lang => ru,
     *      i_time_zone => 274,
     *      customer_name => 5500910000000001320321,
     *      billing_model => 1,
     *      follow_me_enabled => N,
     *      product_name => MCN_telecom,
     *      sip_id => 79587980262
     * ]]
     * @throws \yii\base\InvalidParamException
     * @throws \app\exceptions\ModelValidationException
     */
    public static function getAccountDataCallback($params)
    {
        $accountTariff = AccountTariff::findOne(['id' => $params['requestId']]);
        if (!$accountTariff) {
            throw new InvalidParamException('Неправильный ID услуги: ' . print_r($params, true));
        }

        if (
            !isset($params['data']['customer_name'])
            || !($accountTariff->mtt_number = $params['data']['customer_name'])
        ) {
            throw new InvalidParamException('Неправильный MTT ID: ' . print_r($params, true));
        }

        if (!$accountTariff->save()) {
            throw new ModelValidationException($accountTariff);
        }
    }

    /**
     * Callback обработчик API-запроса balanceAdjustment
     *
     * @param array $params [requestId => 100123]
     * @throws \yii\base\InvalidParamException
     * @throws \LogicException
     */
    public static function balanceAdjustmentCallback($params)
    {
    }
}
