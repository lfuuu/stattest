<?php

namespace app\modules\notifier\components;

use Exception;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\db\Query;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use app\classes\Assert;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\notifier\models\Schemes;
use app\modules\notifier\Module as Notifier;
use app\models\ClientContact;
use app\models\important_events\ImportantEventsNames;
use app\models\LkNoticeSetting;
use app\models\User;
use app\modules\notifier\models\Logger;

class Actions extends Component
{

    const MAILER_EVENTS_READ = '/site/get-personal-scheme';
    const MAILER_EVENTS_UPDATE = '/site/apply-personal-scheme';
    const MAILER_WHITELIST_READ = '/site/white-list';
    const MAILER_WHITELIST_UPDATE = '/site/white-list-set';
    const MAILER_SCHEME_APPLY = '/site/apply-scheme';

    /**
     * @param int $clientAccountId
     * @return mixed
     * @throws Exception
     */
    public function getPersonalSchemeForClientAccount($clientAccountId)
    {
        /** @var Notifier $notifier */
        $notifier = Notifier::getInstance();
        return $notifier->send(self::MAILER_EVENTS_READ . '?clientAccountId=' . $clientAccountId);
    }

    /**
     * @return array|null
     * @throws ErrorException
     * @throws Exception
     */
    public function getWhiteList()
    {
        /** @var Notifier $notifier */
        $notifier = Notifier::getInstance();
        $result = $notifier->send(self::MAILER_WHITELIST_READ);
        return $result['count'] ? ArrayHelper::map($result['result'], 'event_type_id', 'created_at') : null;
    }

    /**
     * @param array $whitelist
     */
    public function applyWhiteList(array $whitelist = [])
    {
        $log = new Logger;
        $log->user_id = \Yii::$app->user->getId() ?: User::SYSTEM_USER_ID;
        $log->action = Logger::ACTION_APPLY_WHITELIST;
        $log->created_at = date(DateTimeZoneHelper::DATETIME_FORMAT);

        if ($log->save()) {
            /** @var Notifier $notifier */
            $notifier = Notifier::getInstance();
            $notifier->send(self::MAILER_WHITELIST_UPDATE, $whitelist);
        }
    }

    /**
     * @param int $clientAccountId
     */
    public function applySchemeForClientAccount($clientAccountId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);
        Assert::isObject($clientAccount);

        $scheme = Schemes::find()
            ->where(['country_code' =>  $clientAccount->contract->contragent->country_id])
            ->all();

        $requestData = [];

        foreach ($scheme as $record) {
            foreach (Schemes::$types as $type) {
                $requestData[$record->event] = [
                    $type => $record->{$type},
                ];
            }
        }

        /** @var Notifier $notifier */
        $notifier = Notifier::getInstance();
        $notifier->send(self::MAILER_EVENTS_UPDATE . '?clientAccountId=' . $clientAccountId, $requestData);
    }

    /**
     * @param int $clientAccountId
     * @param array $requestData
     * @return mixed
     * @throws Exception
     * @throws ErrorException
     */
    public function applyPersonalSchemeForClientAccount($clientAccountId, $requestData)
    {
        /** @var Notifier $notifier */
        $notifier = Notifier::getInstance();
        return $notifier->send(self::MAILER_EVENTS_UPDATE . '?clientAccountId=' . $clientAccountId, $requestData);
    }

    /**
     * @param int $clientAccountId
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function applyPersonalLkSchemeForClientAccount($clientAccountId)
    {
        /** @var Notifier $notifier */
        $notifier = Notifier::getInstance();

        $requestData = [];

        foreach (LkNoticeSetting::$noticeTypes as $type => $typeInLk) {
            $settings = (new Query)
                ->select([
                    ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE => new Expression('MAX(ns.min_balance)'),
                    ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT => new Expression('MAX(ns.min_day_limit)'),
                    ImportantEventsNames::IMPORTANT_EVENT_PAYMENT_ADD => new Expression('MAX(ns.add_pay_notif)'),
                ])
                ->from(['ns' => LkNoticeSetting::tableName()])
                ->leftJoin(['cc' => ClientContact::tableName()], 'cc.id = ns.client_contact_id')
                ->where([
                    'ns.status' => LkNoticeSetting::STATUS_WORK,
                    'ns.client_id' => $clientAccountId,
                    'cc.type' => $typeInLk,
                ])
                ->one();

            foreach ($settings as $name => $value) {
                $requestData[$name]['do_' . $type . '_personal'] = $value;
            }
        }

        try {
            $notifier->send(self::MAILER_EVENTS_UPDATE . '?clientAccountId=' . $clientAccountId, $requestData);
        }
        catch (\Exception $e) {
            throw new $e;
        }

        return true;
    }

    /**
     * @param int $countryCode
     * @param int $userId
     * @return mixed
     * @throws ErrorException
     * @throws Exception
     */
    public function applySchemeForCountry($countryCode, $userId = User::SYSTEM_USER_ID)
    {
        $scheme = Schemes::findAll(['country_code' => $countryCode]);
        $requestScheme = [];

        foreach ($scheme as $record) {
            foreach (Schemes::$types as $type) {
                $requestScheme[$record->event][$type] = $record->{$type};
            }
        }

        $requestData = [
            'clients' => Schemes::findClientInCountry($countryCode)->select('client.id')->column(),
            'scheme' => $requestScheme,
        ];

        if (count($requestScheme) && count($requestData['clients'])) {
            $log = new Logger;
            $log->user_id = $userId;
            $log->action = Logger::ACTION_APPLY_SCHEME;
            $log->value = $countryCode;
            $log->created_at = date(DateTimeZoneHelper::DATETIME_FORMAT);

            if ($log->save()) {
                /** @var Notifier $notifier */
                $notifier = Notifier::getInstance();
                return $notifier->send(self::MAILER_SCHEME_APPLY . '?countryCode=' . $countryCode, $requestData);
            }
        }

        return false;
    }

}
