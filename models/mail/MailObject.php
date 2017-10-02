<?php

namespace app\models\mail;

use app\classes\model\ActiveRecord;

/**
 * Class MailObject
 * @package app\models
 *
 * @property int object_id
 * @property int job_id
 * @property int client_id
 * @property string object_type
 * @property string object_param
 * @property string source
 * @property int view_count
 * @property string view_ts
 */
class MailObject extends ActiveRecord
{

    public static function tableName()
    {
        return 'mail_object';
    }
}
