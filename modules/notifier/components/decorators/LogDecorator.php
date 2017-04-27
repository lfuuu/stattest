<?php

namespace app\modules\notifier\components\decorators;

use app\helpers\DateTimeZoneHelper;
use app\models\User;
use yii\base\Model;
use yii\helpers\Json;

/**
 * @property string $action
 * @property string $timestamp
 * @property int $country_code
 * @property int $client_account_id
 * @property int $user_id
 * @property string $result
 * @property string $message
 *
 * @property string $userName
 * @property string $updated
 */
class LogDecorator extends Model
{

    /** @var string */
    public $action;

    /** @var string */
    public $timestamp;

    /** @var int */
    public $country_code;

    /** @var int */
    public $client_account_id;

    /** @var string */
    public $result;

    /** @var string */
    public $message;

    /** @var int */
    public $user_id;

    /**
     * @inheritdoc
     */
    public function init()
    {
        try {
            $data = Json::decode($this->result);
            if (!is_null($data)) {
                $this->setAttributes($data);
            }
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
        }
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['user_id', 'integer'],
            ['message', 'string'],
        ];
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return ((int)$this->user_id && ($user = User::findOne(['id' => $this->user_id]))) ?
            $user->name :
            '';
    }

    /**
     * @return string
     */
    public function getUpdated()
    {
        return (new \DateTime($this->timestamp))
            ->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
    }

}