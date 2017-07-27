<?php

namespace app\models;

use app\classes\behaviors\EventQueueAddEvent;
use app\classes\Event;
use app\classes\model\ActiveRecord;
use app\queries\LkNoticeSettingQuery;

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

    const NOTIFICATION_TYPE_EMAIL = 'email';
    const NOTIFICATION_TYPE_SMS = 'sms';

    /** @var array */
    public static $noticeTypes = [
        self::NOTIFICATION_TYPE_EMAIL => 'email',
        self::NOTIFICATION_TYPE_SMS => 'phone',
    ];

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'EventQueueAddEvent' => [
                'class' => EventQueueAddEvent::className(),
                'insertEvent' => Event::LK_SETTINGS_TO_MAILER,
                'updateEvent' => Event::LK_SETTINGS_TO_MAILER,
                'idField' => 'client_id',
            ],
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
