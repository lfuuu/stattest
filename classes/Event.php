<?php
namespace app\classes;

use app\models\EventQueue;

class Event
{
    /**
     * Функция добавления события в очередь обработки
     *
     * @param string $event Название собятия
     * @param string|array $param Данные для обработки собятия
     * @param bool $isForceAdd Принудительное добавления собятия. (Если событие уже есть в очереди, то оно не добавляется)
     */
    public static function go($event, $param = "", $isForceAdd = false)
    {
        if (is_array($param)) {
            $param = json_encode($param);
        }

        $code = md5($event . "|||" . $param);

        $row = null;
        if (!$isForceAdd) {
            $row =
                EventQueue::find()
                    ->andWhere(['code' => $code])
                    ->andWhere("status not in ('ok', 'stop')")
                    ->limit(1)
                    ->one();
        }

        if (!$row) {
            $row = new EventQueue();
            $row->event = $event;
            $row->param = $param;
            $row->code = $code;
        } else {
            $row->iteration = 0;
            $row->status = 'plan';
        }
        $row->save();
    }
}