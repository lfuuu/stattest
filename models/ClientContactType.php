<?php
namespace app\models;

use app\helpers\DateTimeZoneHelper;
use yii\db\ActiveRecord;

/**
 * Class ClientContactType
 * @package app\models
 *
 * @property int id
 * @property string code
 * @property string name
 */
class ClientContactType extends ActiveRecord
{
    const TYPE_ID_PHONE = 1;
    const TYPE_ID_EMAIL = 2;
    const TYPE_ID_FAX = 3;
    const TYPE_ID_SMS = 150;
    const TYPE_ID_EMAIL_INVOICE = 201;
    const TYPE_ID_EMAIL_RATE = 202;
    const TYPE_ID_EMAIL_SUPPORT = 203;

    const TYPE_PHONE = 'phone';
    const TYPE_EMAIL = 'email';
    const TYPE_FAX = 'fax';
    const TYPE_SMS = 'sms';
    const TYPE_EMAIL_INVOICE = 'email_invoice';
    const TYPE_EMAIL_RATE = 'email_rate';
    const TYPE_EMAIL_SUPPORT = 'email_support';


    public static function tableName()
    {
        return 'client_contact_type';
    }

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'trim'],
        ];
    }

    public function __toString()
    {
        return $this->name;
    }

}
