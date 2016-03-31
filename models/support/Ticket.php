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
 * @property string $department
 * @property string $created_at
 * @property string $updated_at
 * @property
 */
class Ticket extends ActiveRecord
{
    const TROUBLE_STATE_OPEN = 1;
    const TROUBLE_STATE_DONE = 7;
    const TROUBLE_STATE_CLOSED = 2;
    const TROUBLE_STATE_REOPENED = 51;

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
        switch ($state_id) {
            case self::TROUBLE_STATE_CLOSED: {
                $this->status = TicketStatusEnum::CLOSED;
                break;
            }
            case self::TROUBLE_STATE_DONE: {
                $this->status = TicketStatusEnum::DONE;
                break;
            }
            case self::TROUBLE_STATE_REOPENED: {
                $this->status = TicketStatusEnum::REOPENED;
                break;
            }
            default: {
                $this->status = TicketStatusEnum::OPEN;
            }
        }
    }

    public function spawnTroubleStatus()
    {
        switch ($this->status) {
            case TicketStatusEnum::CLOSED:
                return self::TROUBLE_STATE_CLOSED;
            case TicketStatusEnum::DONE:
                return self::TROUBLE_STATE_DONE;
            case TicketStatusEnum::REOPENED:
                return self::TROUBLE_STATE_REOPENED;
            default:
                return self::TROUBLE_STATE_OPEN;
        }
    }

}