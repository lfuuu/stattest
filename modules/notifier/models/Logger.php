<?php

namespace app\modules\notifier\models;

use app\helpers\DateTimeZoneHelper;
use app\models\User;
use yii\db\ActiveRecord;

/**
 * @property int $user_id
 * @property string $action
 * @property string $value
 * @property string $created_at
 * @property string $result
 * @property string $updated_at
 */
class Logger extends ActiveRecord
{

    const ACTION_APPLY_SCHEME = 'applyGlobalScheme';
    const ACTION_APPLY_WHITELIST = 'applyWhitelist';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'notifier_log';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['action', 'value', 'created_at'], 'string'],
            [['user_id', ], 'integer'],
            [['user_id', 'action',], 'required'],
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $createdAt = (new \DateTime($this->created_at, new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        return (string)User::findOne($this->user_id)->name . ' (' . $createdAt . ')';
    }

}
