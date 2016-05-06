<?php
namespace app\classes\enum;

use app\classes\Enum;

class TicketStatusEnum extends Enum
{
    const OPEN = 'open';
    const DONE = 'done';
    const CLOSED = 'closed';
    const REOPENED = 'reopened';

    protected static $names = [
        self::OPEN => 'Открыт',
        self::DONE => 'Выполнен',
        self::CLOSED => 'Закрыт',
        self::REOPENED => 'Открыт повторно',
    ];
}