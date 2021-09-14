<?php

namespace app\classes\notification\processors;


use app\classes\LkNotification;
use app\models\ClientAccount;
use app\models\EventQueue;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\LkClientSettings;
use app\models\LkNoticeSetting;
use app\models\LkNotificationLog;
use app\models\Param;
use RuntimeException;

abstract class NotificationProcessor
{
    const COMPARISON_LEVEL_NO_EQUAL = 1;
    const COMPARISON_LEVEL_WITH_EQUAL = 2;

    /**
     * @var \app\queries\ClientAccountQuery $clients
     */
    protected $clients = null;

    /**
     * @var \app\models\ClientAccount $client
     */
    protected $client = null;

    protected $eventFields = [];

    public function __construct()
    {
        $this->clients = ClientAccount::find()->select([
            'id',
            'credit',
            'voip_credit_limit_day',
            'voip_limit_mn_day'
        ])->active();
    }

    /**
     * Возвращает событие входа в области проверяемого значения
     *
     * @return string
     */
    abstract function getEnterEvent();

    /**
     * Возвращает событие выхода из области проверяемого значения
     *
     * @return string
     */
    abstract function getLeaveEvent();

    /**
     * Получение величины значения
     *
     * @return float
     */
    abstract function getValue();

    /**
     * Получение лимита значения
     *
     * @return float
     */
    abstract function getLimit();

    /**
     * Должно ли значение быть больше лимита
     * Сравниваемое значение в "нормальном" состоянии должно быть больше лимита (баланс и минимальный лимит), или наоборт (дневной лимит и дневное потредление)
     *
     * @return bool
     */
    protected function isPositiveComparison()
    {
        return true;
    }

    /**
     * Тип сравнения
     * Больше/меньше или больше/меньше-и-равно
     *
     * @return int
     */
    protected function comparisonLevel()
    {
        return self::COMPARISON_LEVEL_WITH_EQUAL;
    }

    /**
     * Пропустить обработку, если лимит не задан.
     * "Заданость" лимита определяет обработчик
     *
     * @return bool
     */
    protected function checkLimitToSkip()
    {
        return false;
    }

    /**
     * Надо ли отправлять оповещение напрямую
     *
     * @return bool
     */
    public function isLocalSeviceNotification()
    {
        return true;
    }

    /**
     * Фильтр по клиентам
     *
     * @return $this
     */
    public function filterClients()
    {
        $dayLimitClients = LkNoticeSetting::find()
            ->distinct()
            ->select('client_id')
            ->where([
                $this->getEnterEvent() => 1
            ])
            ->andWhere(['status' => LkNoticeSetting::STATUS_WORK]);

        $this->clients->innerJoin(['dl' => $dayLimitClients], 'dl.client_id = ' . ClientAccount::tableName() . '.id');

        return $this;
    }

    /**
     * Установка ЛС
     *
     * @param ClientAccount $client
     * @return $this
     */
    public function setClient(ClientAccount $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Проверка входа/выхода значения в зону лимита и оповещение.
     */
    public function checkAndMakeNotifications()
    {
        $count = 0;
        foreach ($this->clients->each() as $client) {
            if (++$count % 1000 == 0) {
                if (Param::find()->where(['param' => Param::NOTIFICATIONS_SWITCH_ON_DATE])->exists()) {
                    throw new RuntimeException('[lk/check-notification][-] Оповещения отключены');
                }
            }
            $transaction = \Yii::$app->getDb()->beginTransaction();
            try {
                $this->compareAndNotificationClient($client);
            } catch (\Exception $e) {
                echo PHP_EOL . date('r') . ': (!)' . $client->id . ", " . $this->getEnterEvent() . ', error: ' . $e->getMessage();
                $transaction->rollBack();
                \Yii::error($e->getMessage());
                continue;
            }
            $transaction->commit();
        }
    }

    /**
     * Сравнение и оповещение клиента
     *
     * @param ClientAccount $client
     */
    public function compareAndNotificationClient(ClientAccount $client)
    {
        $this->setClient($client);

        $isUnSet = false;

        ($isSet = $this->compareSet()) || ($isUnSet = $this->compareUnSet());

        ClientAccount::getDb()->transaction(function () use ($client, $isSet, $isUnSet) {

            if ($isSet || $isUnSet) {
                LkClientSettings::saveState($client, $this->getEnterEvent(), $isSet);
            }

            if ($isSet) {
                echo "\n" . date('r') . ': (+)' . $client->id . ", " . $this->getEnterEvent() . ', balance: ' . $client->billingCounters->realtimeBalance . ', day: ' . $client->billingCounters->daySummary . ', limit: ' . $this->getLimit() . ', value: ' . $this->getValue();
                $this->createImportantEventSet($client, true, $this->getEnterEvent());
                if ($this->isLocalSeviceNotification()) {
                    $this->oldSetupSendAndSaveLog();
                }
            }

            if ($isUnSet) {
                echo "\n" . date('r') . ': (-)' . $client->id . ", " . $this->getEnterEvent() . ', balance: ' . $client->billingCounters->realtimeBalance . ', day: ' . $client->billingCounters->daySummary . ', limit: ' . $this->getLimit() . ', value: ' . $this->getValue();
                $this->createImportantEventSet($client, false, $this->getLeaveEvent());
                if ($this->isLocalSeviceNotification()) {
                    $this->oldUnsetSaveLog();
                }
            }
        });
    }

    /**
     * Создание события. Для обработчиков, проверющих переход через лимит.
     *
     * @param ClientAccount $client
     * @param bool $isSet
     * @param string $event
     */
    private function createImportantEventSet(ClientAccount $client, $isSet = true, $event = '')
    {
        ImportantEvents::create(
            $event,
            ImportantEventsSources::SOURCE_STAT, [
                'client_id' => $client->id,
                'value' => $this->getValue(),
                'limit' => $this->getLimit(),
                'user_id' => \Yii::$app->user->id,
                'is_set' => $isSet ? 1 : 0
            ] + $this->eventFields);

        if ($isSet && $event == ImportantEventsNames::ZERO_BALANCE) {
            EventQueue::go(ImportantEventsNames::ZERO_BALANCE, ['account_id' => $client->id, 'value' => round($this->getValue(), 2), 'limit' => $this->getLimit()]);
        }
    }

    /**
     * Создание события для "без лимитных" обработчиков.
     */
    public function makeSingleClientNotification()
    {
        if ($this->getContactsForSend()) {
            ImportantEvents::create($this->getEnterEvent(), ImportantEventsSources::SOURCE_STAT, [
                    'client_id' => $this->client->id,
                    'value' => $this->getValue(),
                    'user_id' => \Yii::$app->user->id
                ] + $this->eventFields);

            $this->oldSetupSendAndSaveLog();
        }
    }

    /**
     * Старая система оповещения, и сохранения в лог оповещений
     */
    private function oldSetupSendAndSaveLog()
    {
        /** Оправка самих сообщений по старому */
        foreach ($this->getContactsForSend() as $contact) {
            $balance = round($this->client->billingCountersFastMass->realtimeBalance, 2);

            $Notification = new LkNotification(
                $this->client->id, $contact->id,
                $this->getEnterEvent(), $this->getValue(), $balance
            );
            if ($Notification->send()) {
                $this->oldAddLogRaw(
                    $this->client->id, $contact->id,
                    $this->getEnterEvent(), true,
                    $balance, $this->getLimit(), $this->getValue()
                );

            }
        }
    }

    /**
     * Подготовка и сохранение записи, о оповещении клиента.
     */
    private function oldUnsetSaveLog()
    {
        $this->oldAddLogRaw($this->client->id, 0, $this->getEnterEvent(), false,
            sprintf('%0.2f', $this->client->billingCounters->realtimeBalance), $this->getLimit(),
            $this->getValue());
    }

    /**
     * Сохранение записи, о оповещении клиента.
     *
     * @param $clientId
     * @param $contactId
     * @param $event
     * @param $isSet
     * @param $balance
     * @param $limit
     * @param $value
     */
    private function oldAddLogRaw($clientId, $contactId, $event, $isSet, $balance, $limit, $value)
    {
        LkNotificationLog::addLogRaw($clientId, $contactId, $event, $isSet, $balance, $limit, $value);
    }

    /**
     * Получаем контакты для отправки
     *
     * @return array|\app\models\ClientContact[]
     */
    protected function getContactsForSend()
    {
        $contacts = [];

        /** @var \app\models\LkNoticeSetting $noticeSetting */
        foreach ($this->client->getLkNoticeSetting()->andWhere([
            $this->getEnterEvent() => 1,
            'status' => LkNoticeSetting::STATUS_WORK
        ])->all() as $noticeSetting) {
            $contact = $noticeSetting->contact;
            if ($contact) {
                $contacts[] = $contact;
            }
        }

        return $contacts;
    }

    /**
     * Наступило ли событие перехода значения через лимит
     *
     * @return bool
     */
    public function compareSet()
    {
        $value = $this->getValue();
        $limit = $this->getLimit();

        if ($this->checkLimitToSkip($limit)) {
            return false;
        }


        if ($this->isPositiveComparison()) {
            if ($this->comparisonLevel() == self::COMPARISON_LEVEL_WITH_EQUAL) {
                $isCompareSet = $value <= $limit;
            } else { //self::COMPARISON_LEVEL_NO_EQUAL
                $isCompareSet = $value < $limit;
            }
        } else {
            if ($this->comparisonLevel() == self::COMPARISON_LEVEL_WITH_EQUAL) {
                $isCompareSet = $value >= $limit;
            } else {
                $isCompareSet = $value > $limit;
            }
        }

        if ($isCompareSet) {
            $lkSettings = $this->client->lkClientSettings;
            if (!$lkSettings || !$lkSettings->{'is_' . $this->getEnterEvent() . '_sent'}) {
                return true;
            }
        }

        return false;
    }

    /**
     * Наступило ли событие возвращения значения через лимит
     *
     * @return bool
     */
    public function compareUnSet()
    {
        $value = $this->getValue();
        $limit = $this->getLimit();

        if ($this->checkLimitToSkip($limit)) {
            return false;
        }

        if ($this->isPositiveComparison()) {
            if ($this->comparisonLevel() == self::COMPARISON_LEVEL_WITH_EQUAL) {
                $isCompareUnset = $value > $limit;
            } else {
                $isCompareUnset = $value >= $limit;
            }
        } else {
            if ($this->comparisonLevel() == self::COMPARISON_LEVEL_WITH_EQUAL) {
                $isCompareUnset = $value < $limit;
            } else {
                $isCompareUnset = $value <= $limit;
            }
        }

        if ($isCompareUnset) {
            $lkSettings = $this->client->lkClientSettings;
            if ($lkSettings && $lkSettings->{'is_' . $this->getEnterEvent() . '_sent'}) {
                return true;
            }
        }
        return false;
    }

}