<?php

namespace app\modules\notifier\components;

use app\classes\Assert;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\important_events\ImportantEventsNames;
use app\models\LkNoticeSetting;
use app\models\User;
use app\modules\notifier\components\decorators\SchemePersonalDecorator;
use app\modules\notifier\models\Schemes;
use Exception;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Url;

class Actions extends Component
{

    const MAILER_WHITELIST_GET = '/site/get-whitelist';
    const MAILER_WHITELIST_APPLY = '/site/apply-whitelist';

    const MAILER_SCHEME_GET = '/site/get-scheme';
    const MAILER_SCHEME_APPLY = '/site/apply-scheme';

    const MAILER_SCHEME_PERSONAL_GET = '/site/get-scheme-personal';
    const MAILER_SCHEME_PERSONAL_APPLY = '/site/apply-scheme-personal';

    /** @var \app\modules\notifier\Module */
    public $module;

    /**
     * @return array
     * @throws ErrorException
     * @throws Exception
     */
    public function getWhiteList()
    {
        $requestUrl = self::MAILER_WHITELIST_GET;
        return (array)$this->module->send($requestUrl);
    }

    /**
     * @param int $countryCode
     * @return mixed
     * @throws InvalidParamException
     * @throws Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function getScheme($countryCode)
    {
        $requestUrl = Url::to([self::MAILER_SCHEME_GET, 'countryCode' => $countryCode]);
        return $this->module->send($requestUrl);
    }

    /**
     * @param array $whitelist
     * @param int $userId
     * @return mixed
     * @throws InvalidParamException
     * @throws Exception
     */
    public function applyWhiteList(array $whitelist = [], $userId = User::SYSTEM_USER_ID)
    {
        $requestUrl = Url::to([self::MAILER_WHITELIST_APPLY, 'userId' => $userId]);
        return $this->module->send($requestUrl, $whitelist);
    }

    /**
     * @param int $countryCode
     * @param int $userId
     * @return mixed
     * @throws InvalidParamException
     * @throws ErrorException
     * @throws Exception
     */
    public function applyScheme($countryCode, $userId = User::SYSTEM_USER_ID)
    {
        $scheme = Schemes::findAll(['country_code' => $countryCode]);
        $requestScheme = [];
        $requestUrl = Url::to([self::MAILER_SCHEME_APPLY, 'countryCode' => $countryCode, 'userId' => $userId]);

        foreach ($scheme as $record) {
            foreach (Schemes::$types as $type) {
                $requestScheme[$record->event][$type] = $record->{$type};
            }
        }

        return $this->module->send($requestUrl, $requestScheme);
    }

    /**
     * @param int $clientAccountId
     * @return mixed
     * @throws InvalidParamException
     * @throws Exception
     */
    public function getSchemePersonal($clientAccountId)
    {
        $requestUrl = Url::to([self::MAILER_SCHEME_PERSONAL_GET, 'clientAccountId' => $clientAccountId]);
        return $this->module->send($requestUrl);
    }

    /**
     * @param int[] $clientAccountIds
     * @return mixed
     * @throws InvalidParamException
     * @throws Exception
     */
    public function getSchemePersonalByIds(array $clientAccountIds = [])
    {
        return $this->module->send(self::MAILER_SCHEME_PERSONAL_GET, $clientAccountIds);
    }

    /**
     * @param int $clientAccountId
     * @param array $scheme
     * @param int $userId
     * @return mixed
     * @throws InvalidParamException
     * @throws Exception
     */
    public function applySchemePersonal($clientAccountId, array $scheme, $userId = User::SYSTEM_USER_ID)
    {
        $requestScheme = [];
        $requestUrl = Url::to([self::MAILER_SCHEME_PERSONAL_APPLY, 'clientAccountId' => $clientAccountId, 'userId' => $userId]);

        foreach ($scheme as $eventTypeId => $data) {
            $row = [];
            foreach ($data as $eventType => $value) {
                $row[$eventType] = $value == -1 ? null : $value;
            }
            if (count($row)) {
                $requestScheme[$eventTypeId] = $row;
            }
        }

        return $this->module->send($requestUrl, $requestScheme);
    }

    /**
     * @param int $clientAccountId
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     * @throws InvalidParamException
     */
    public function applySchemePersonalSubscribe($clientAccountId)
    {
        $requestScheme = [];

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
                ])->one();

            foreach ($settings as $name => $value) {
                $requestScheme[$name]['do_' . $type . '_personal'] = $value;
            }
        }

        return $this->applySchemePersonal($clientAccountId, $requestScheme, User::SYSTEM_USER_ID);
    }

}
