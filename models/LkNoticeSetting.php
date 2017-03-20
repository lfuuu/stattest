<?php
namespace app\models;

use app\classes\behaviors\lk\LkNoticeSettings;
use app\models\important_events\ImportantEventsNames;
use app\queries\LkNoticeSettingQuery;
use yii\db\ActiveRecord;

/**
 * Настройки оповещений клиента в ЛК
 *
 * @property ClientContact $contact
 * @property int $client_contact_id
 * @property int $client_id
 * @property int $min_balance
 * @property int $min_day_limit
 * @property int $add_pay_notif
 * @property string $status
 * @property string $activate_code
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
}
