<?php

namespace app\models\support;

use app\classes\behaviors\CreatedAt;
use app\classes\enum\TicketStatusEnum;
use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property int $account_id
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
    const TROUBLE_STATE_REOPENED = 50;

    private $stateIdToStatus = [
        self::TROUBLE_STATE_CLOSED => TicketStatusEnum::CLOSED,
        self::TROUBLE_STATE_DONE => TicketStatusEnum::DONE,
        self::TROUBLE_STATE_REOPENED => TicketStatusEnum::REOPENED,
    ];

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
        $this->status = TicketStatusEnum::OPEN;

        if (isset($this->stateIdToStatus[$state_id])) {
            $this->status = $this->stateIdToStatus[$state_id];
        }
    }

    public function spawnTroubleStatus()
    {
        $statuses = array_flip($this->stateIdToStatus);

        if (isset($statuses[$this->status])) {
            return $statuses[$this->status];
        }

        return self::TROUBLE_STATE_OPEN;
    }

}