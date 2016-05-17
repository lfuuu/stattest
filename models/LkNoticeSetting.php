<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\queries\LkNoticeSettingQuery;

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
 *
 * @package app\models
 */
class LkNoticeSetting extends ActiveRecord
{
    const STATUS_WORK = 'working';
    const STATUS_CONNECT = 'connecting';

    public static function tableName()
    {
        return 'lk_notice_settings';
    }

    public static function find()
    {
        return new LkNoticeSettingQuery(get_called_class());
    }

    public function getContact()
    {
        return $this->hasOne(ClientContact::className(), ['id' => 'client_contact_id']);
    }
}
