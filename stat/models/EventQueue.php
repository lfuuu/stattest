<?php
use app\classes\HttpClientLogger;

/**
 * @property int $id
 * @property string $date timestamp
 * @property string $event
 * @property string $param
 * @property string $status    enum('plan','ok','error','stop')
 * @property int $iteration
 * @property string $next_start timestamp
 * @property string $log_error
 * @property string $code
 * @property string $trace
 */
class EventQueue extends ActiveRecord\Model
{
    static $table_name = 'event_queue';

    /**
     * @return mixed
     */
    public static function getPlanedEvents()
    {
        return self::find('all', [
                'conditions' => ['status' => 'plan'],
                'order' => 'id'
            ]
        );
    }

    /**
     * @return mixed
     */
    public static function getPlanedErrorEvents()
    {
        return self::find('all', [
            'conditions' => ['status = ? and next_start < NOW()', ['error']],
            'order' => 'id'
        ]);
    }

    /**
     * Устанавливаем успешное завершение задачи
     *
     * @param string $info
     */
    public function setOk($info = '')
    {
        if ($info) {
            $this->log_error = $info;
        }

        $httpClientLogger = HttpClientLogger::me();
        $logs = $httpClientLogger->get();
        if ($logs) {
            $this->trace = implode(PHP_EOL . PHP_EOL, $logs);
        }

        $this->status = 'ok';
        $this->save();
    }

    /**
     * Устанавливаем завершение задачи с ошибкой
     *
     * @param Exception|null $e
     * @param bool $isStop
     */
    public function setError(Exception $e = null, $isStop = false)
    {
        if ($isStop) {
            $this->status = \app\models\EventQueue::STATUS_STOP;
        } else {
            list($this->status, $this->next_start) = self::_setNextStart($this);
        }

        $this->iteration++;

        if ($e) {
            $this->log_error = $e->getCode() . ': ' . $e->getMessage();

            $httpClientLogger = HttpClientLogger::me();
            $logs = $httpClientLogger->get();
            if ($logs) {
                $this->trace = implode(PHP_EOL . PHP_EOL, $logs) . PHP_EOL . PHP_EOL;
            } else {
                $this->trace = '';
            }

            $this->trace .= $e->getFile() . ':' . $e->getLine() . ';\n ' . $e->getTraceAsString();

            Yii::error($e);
        }

        $this->save();
    }

    /**
     * Устанавливаем время следующего запуска задачи
     *
     * @param EventQueue $o
     * @return array
     */
    private static function _setNextStart(EventQueue $o)
    {
        switch ($o->iteration) {
            case 0:
                $time = '+1 minute';
                break;
            case 1:
                $time = '+2 minute';
                break;
            case 2:
                $time = '+3 minute';
                break;
            case 3:
                $time = '+5 minute';
                break;
            case 4:
                $time = '+10 minute';
                break;
            case 5:
                $time = '+20 minute';
                break;
            case 6:
                $time = '+30 minute';
                break;
            case 7:
                $time = '+1 hour';
                break;
            case 8:
                $time = '+2 hour';
                break;
            case 9:
                $time = '+3 hour';
                break;
            case 10:
                $time = '+6 hour';
                break;
            case 11:
                $time = '+12 hour';
                break;
            case 12:
                $time = '+1 day';
                break;
            case 13:
                $time = '+1 day';
                break;
            case 14:
                $time = '+1 day';
                break;
            case 15:
                $time = '+1 day';
                break;
            case 16:
                $time = '+1 day';
                break;
            case 17:
                $time = '+1 day';
                break;
            case 18:
                $time = '+1 day';
                break;
            default:
                return ['stop', date('Y-m-d H:i:s')];
        }

        return ['error', date('Y-m-d H:i:s', strtotime($time))];
    }

    /**
     * Очистка очереди
     */
    public static function clean()
    {
        EventQueue::table()->conn->query('delete from event_queue where date < date_sub(now(), INTERVAL 3 month)');
    }
}
