<?php

namespace app\classes\notification\processors;


use app\classes\LkNotification;
use app\models\ClientAccount;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsSources;
use app\models\LkClientSettings;
use app\models\LkNoticeSetting;
use app\models\LkNotificationLog;

abstract class NotificationProcessor
{

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
        $this->clients = ClientAccount::find()->active();
    }

    abstract function getEvent();

    abstract function getValue();

    abstract function getLimit();

    protected function isPositiveComparison()
    {
        return true;
    }

    public function filterClients()
    {
        $dayLimitClients = LkNoticeSetting::find()
            ->distinct()
            ->active()
            ->select('client_id')
            ->where([
                $this->getEvent() => 1
            ]);

        $this->clients->innerJoin(['dl' => $dayLimitClients], 'dl.client_id = ' . ClientAccount::tableName() . '.id');

        return $this;
    }

    public function setClient(ClientAccount $client)
    {
        $this->client = $client;
        return $this;
    }

    public function checkAndMakeNotifications()
    {
        foreach ($this->clients->all() as $client) {
            $this->compareAndNotificationClient($client);
        }
    }

    public function compareAndNotificationClient(ClientAccount $client)
    {
        $this->setClient($client);

        $isUnSet = false;

        ($isSet = $this->compareSet()) || ($isUnSet = $this->compareUnSet());

        ClientAccount::getDb()->transaction(function () use ($client, $isSet, $isUnSet) {

            if ($isSet || $isUnSet) {
                LkClientSettings::saveState($client, $this->getEvent(), $isSet);
            }

            if ($isSet) {
                echo "\n" . date('r') . ': (+)' .  $client->id . ", " . $this->getEvent() . ', balance: ' . $client->getRealtimeBalance() . ', day: ' . $client->getDaySum() . ', limit: ' . $this->getLimit() . ', value: ' . $this->getValue();
                $this->createImportantEventSet($client, true);
                $this->oldSetupSendAndSaveLog();
            }

            if ($isUnSet) {
                echo "\n" . date('r') . ': (-)' .  $client->id . ", " . $this->getEvent() . ', balance: ' . $client->getRealtimeBalance() . ', day: ' . $client->getDaySum() . ', limit: ' . $this->getLimit() . ', value: ' . $this->getValue();
                $this->createImportantEventSet($client, false);
                $this->oldUnsetSaveLog();
            }
        });
    }

    private function createImportantEventSet(ClientAccount $client, $isSet = true)
    {
        ImportantEvents::create($this->getEvent(), ImportantEventsSources::SOURCE_STAT, [
                'client_id' => $client->id,
                'value' => $this->getValue(),
                'limit' => $this->getLimit(),
                'user_id' => \Yii::$app->user->id,
                'is_set' => $isSet ? 1 : 0
            ] + $this->eventFields);
    }


    public function makeSingleClientNotification()
    {
        if ($this->getContactsForSend()) {
            ImportantEvents::create($this->getEvent(), ImportantEventsSources::SOURCE_STAT, [
                    'client_id' => $this->client->id,
                    'value' => $this->getValue(),
                    'user_id' => \Yii::$app->user->id
                ] + $this->eventFields);

            $this->oldSetupSendAndSaveLog();
        }
    }

    private function oldSetupSendAndSaveLog()
    {
        /** Оправка самих сообщений по старому */
        foreach ($this->getContactsForSend() as $contact) {
            $Notification = new LkNotification($this->client->id, $contact->id, $this->getEvent(), $this->getValue(), $this->client->balance);
            if ($Notification->send()) {
            $this->oldAddLogRaw($this->client->id, $contact->id, $this->getEvent(), true,
                $this->client->balance, $this->getLimit(), $this->getValue());
            }
        }
    }

    private function oldUnsetSaveLog()
    {
        $this->oldAddLogRaw($this->client->id, 0, $this->getEvent(), false, $this->client->balance, $this->getLimit(),
            $this->getValue());
    }

    private function oldAddLogRaw($clientId, $contactId, $event, $isSet, $balance, $limit, $value)
    {
        LkNotificationLog::addLogRaw($clientId, $contactId, $event, $isSet, $balance, $limit, $value);
    }

    /**
     * @return array|\app\models\ClientContact[]
     */
    protected function getContactsForSend()
    {
        $contacts = [];

        /** @var \app\models\LkNoticeSetting $noticeSetting */
        foreach ($this->client->getLkNoticeSetting()->andWhere([
            $this->getEvent() => 1,
            'status' => LkNoticeSetting::STATUS_WORK
        ])->all() as $noticeSetting) {
            $contact = $noticeSetting->contact;
            if ($contact) {
                $contacts[] = $contact;
            }
        }

        return $contacts;
    }

    public function compareSet()
    {
        $value = $this->getValue();
        $limit = $this->getLimit();

        if ($this->isPositiveComparison()) {
            $isCompareSet = $value <= $limit;
        } else {
            $isCompareSet = $value >= $limit;
        }

        if ($isCompareSet) {
            $lkSettings = $this->client->lkClientSettings;
            if (!$lkSettings || !$lkSettings->{'is_' . $this->getEvent() . '_sent'}) {
                return true;
            }
        }

        return false;
    }

    public function compareUnSet()
    {
        $value = $this->getValue();
        $limit = $this->getLimit();

        if ($this->isPositiveComparison()) {
            $isCompareUnset = $value > $limit;
        } else {
            $isCompareUnset = $value < $limit;
        }

        if ($isCompareUnset) {
            $lkSettings = $this->client->lkClientSettings;
            if ($lkSettings && $lkSettings->{'is_' . $this->getEvent() . '_sent'}) {
                return true;
            }
        }
        return false;
    }

}