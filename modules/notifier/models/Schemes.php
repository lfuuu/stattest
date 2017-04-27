<?php

namespace app\modules\notifier\models;

use yii\db\ActiveRecord;

/**
 * @property string $country_code
 * @property string $event
 * @property bool $do_email
 * @property bool $do_sms
 * @property bool $do_email_monitoring
 * @property bool $do_email_operator
 */
class Schemes extends ActiveRecord
{

    const NOTIFICATION_TYPE_EMAIL_MONITORING = 'do_email_monitoring';
    const NOTIFICATION_TYPE_EMAIL_OPERATOR = 'do_email_operator';
    const NOTIFICATION_TYPE_EMAIL = 'do_email';
    const NOTIFICATION_TYPE_SMS = 'do_sms';
    const NOTIFICATION_TYPE_EMAIL_PERSONAL = 'do_email_personal';
    const NOTIFICATION_TYPE_SMS_PERSONAL = 'do_sms_personal';
    const NOTIFICATION_TYPE_LK = 'do_lk';

    public static $types = [
        self::NOTIFICATION_TYPE_EMAIL_MONITORING,
        self::NOTIFICATION_TYPE_EMAIL_OPERATOR,
        self::NOTIFICATION_TYPE_EMAIL,
        self::NOTIFICATION_TYPE_SMS,
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'notifier_schemes';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['event',], 'string'],
            [['country_code', ], 'integer'],
            [['do_email', 'do_sms', 'do_email_monitoring', 'do_email_operator', ], 'boolean'],
            [['country_code', 'event',], 'required'],
        ];
    }

}
