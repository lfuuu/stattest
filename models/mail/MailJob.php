<?php

namespace app\models\mail;

use app\classes\model\ActiveRecord;

/**
 * Class MailJob
 * @package app\models
 *
 * @property int job_id
 * @property string template_subject
 * @property string template_body
 * @property string date_edit
 * @property string user_edit
 * @property string job_state
 * @property string from_email
 */
class MailJob extends ActiveRecord
{

    public static function tableName()
    {
        return 'mail_job';
    }
}
