<?php
namespace app\models;

use app\classes\behaviors\lk\LkNoticeSettings;
use app\classes\HttpClient;
use app\forms\important_events\ImportantEventsNoticesForm;
use app\models\important_events\ImportantEventsNames;
use app\queries\LkNoticeSettingQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * Class LkNoticeSetting
 * Настройки оповещений клиента в ЛК
 *
 * @property ClientContact contact
 * @property int client_contact_id
 * @property int client_id
 * @property int min_balance
 * @property int min_day_limit
 * @property int add_pay_notif
 * @property string status
 * @property string activate_code
 */
class LkNoticeSetting extends ActiveRecord
{
    const STATUS_WORK = 'working';
    const STATUS_CONNECT = 'connecting';

    public static $defaultNotices = [
        ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT,
        ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE,
        ImportantEventsNames::IMPORTANT_EVENT_ADD_PAY_NOTIF,
    ];

    public static $noticeTypes = [
        'email' => 'email',
        'sms' => 'phone',
    ];

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'LkNoticeSettings' => LkNoticeSettings::className(),
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'lk_notice_settings';
    }

    /**
     * @return LkNoticeSettingQuery
     */
    public static function find()
    {
        return new LkNoticeSettingQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(ClientContact::className(), ['id' => 'client_contact_id']);
    }

    /**
     * @param int $clientAccountId
     * @return bool|string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public static function sendToMailer($clientAccountId)
    {
        $config = Yii::$app->params['MAILER'];

        if (!isset($config, $config['url'])) {
            throw new InvalidConfigException('Mailer was not configured');
        }

        $result = [];
        foreach (self::$noticeTypes as $type => $typeInLk) {
            $settings = (new Query)
                ->select([
                    ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE => new Expression('MAX(ns.min_balance)'),
                    ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT => new Expression('MAX(ns.min_day_limit)'),
                    ImportantEventsNames::IMPORTANT_EVENT_PAYMENT_ADD => new Expression('MAX(ns.add_pay_notif)'),
                ])
                ->from(['ns' => LkNoticeSetting::tableName()])
                ->leftJoin([
                    'cc' => ClientContact::tableName(),
                ], 'cc.id = ns.client_contact_id')
                ->where([
                    'ns.status' => LkNoticeSetting::STATUS_WORK,
                    'ns.client_id' => $clientAccountId,
                    'cc.type' => $typeInLk,
                ])
                ->one();

            foreach ($settings as $name => $value) {
                $result[] = [
                    'do_' . $type . '_personal' => $value,
                    'event_code' => $name,
                ];
            }
        }

        $response = (new HttpClient)
            ->createJsonRequest()
            ->setMethod('post')
            ->setData($result)
            ->setUrl($config['url'] . ImportantEventsNoticesForm::MAILER_METHOD_UPDATE . '?clientAccountId=' . $clientAccountId)
            ->auth(isset($config['auth']) ? $config['auth'] : [])
            ->send();

        if (!$response->getIsOk()) {
            throw new BadRequestHttpException($response->getContent());
        }

        return true;
    }
}
