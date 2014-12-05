<?php
namespace app\models\support;

use app\classes\behaviors\CreatedAt;
use app\classes\enum\TicketStatusEnum;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $account_id
 * @property string $user_id
 * @property string $subject
 * @property string $status
 * @property string $service_type
 * @property string $created_at
 * @property string $updated_at
 * @property
 */
class Ticket extends ActiveRecord
{
    const TROUBLE_STATE_OPEN = 1;
    const TROUBLE_STATE_DONE = 7;
    const TROUBLE_STATE_CLOSED = 2;

    public static function tableName()
    {
        return 'support_ticket';
    }

    public function behaviors()
    {
        return [
            'createdAt' => CreatedAt::className(),
        ];
    }

    public function setStatusByTroubleState($state_id)
    {
        if ($state_id == self::TROUBLE_STATE_CLOSED) {
            $this->status = TicketStatusEnum::CLOSED;
        } elseif ($state_id == self::TROUBLE_STATE_DONE) {
            $this->status = TicketStatusEnum::DONE;
        } else {
            $this->status = TicketStatusEnum::OPEN;
        }
    }

    public function spawnTroubleStatus()
    {
        if ($this->status == TicketStatusEnum::CLOSED) {
            return self::TROUBLE_STATE_CLOSED;
        } elseif ($this->status == TicketStatusEnum::DONE) {
            return self::TROUBLE_STATE_DONE;
        } else {
            return self::TROUBLE_STATE_OPEN;
        }
    }

}