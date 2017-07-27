<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class LkNotice
 * Оповещение клиентов
 *
 * @property int $id
 * @property string $type
 * @property string $data
 * @property string $subject
 * @property string $message
 * @property string $created
 * @property int $contact_id
 * @property string $lang
 *
 * @package app\models
 */
class LkNotice extends ActiveRecord
{
    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';

    public static function tableName()
    {
        return 'lk_notice';
    }
}
