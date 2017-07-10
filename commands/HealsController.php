<?php
namespace app\commands;


use app\models\EventQueue;
use app\models\SyncPostgres;
use welltime\graylog\GelfMessage;
use yii\console\Controller;

class HealsController extends Controller
{
    /**
     * Сбор счетчиков
     */
    public function actionIndex()
    {
        foreach ([
                     'z_sync_queue_length',
                     'queue_planed',
                     'server_status',
                     'memory'
                 ] as $healsType) {

            $fnName = '_heals_' . $healsType;

            if (!method_exists($this, $fnName)) {
                throw new \BadMethodCallException('Неизвестный метод: ' . $fnName);
            }

            $value = $this->$fnName();

            if ($value === false) {
                continue;
            }

            $this->logHeals($healsType, $value);
        }
    }

    /**
     * Логирование
     *
     * @param string $healsType
     * @param string|int $value
     */
    public function logHeals($healsType, $value)
    {
        $message = 'Heals for ' . $healsType . ': ' . $value;
        \Yii::info($message, 'heals');
        echo PHP_EOL . date(DATE_ATOM) . ': ' . $message;
    }

    /**
     * Длина очереди на синхронизацию z_sync_postgres
     *
     * @return int
     */
    private function _heals_z_sync_queue_length()
    {
        return SyncPostgres::find()->count();
    }

    /**
     * Длина очереди событий
     *
     * @return int
     */
    private function _heals_queue_planed()
    {
        return EventQueue::find()->where(['status' => EventQueue::STATUS_PLAN])->count();
    }

    /**
     * Состояние сервера
     *
     * @return string
     */
    private function _heals_server_status()
    {
        $status = trim(exec('uptime'));

        // load average: 1,07, 0,82, 0,72 => load average: 1.07, 0.82, 0.72
        $status = strtr($status, [
            ', ' => '@',
            ',' => '.',
            '@' => ', '
        ]);

        return $status;
    }

    /**
     * Состояние памяти
     *
     * @return bool|string
     */
    private function _heals_memory()
    {
        /**
         *             Total        used        free      shared  buff/cache   available
         * Память:     8157836     6688176      230716    527308      1238944    600976
         * Подкачка:   19528700    4649884      14878816
         *
         *       =>
         *
         * в одну строку
         * total: 8157836 used: 6688176 free: 230716 shared: 527308 buff/cache: 1238944 available: 600976
         * swap_total: 19528700 swap_used: 4649884 swap_free: 14878816
         */

        ob_start();
        passthru('free');
        $out = explode("\n", ob_get_clean());

        if (!preg_match_all("/\S+/", $out[0], $titleMatches)) {
            return false;
        }

        if (!preg_match_all("/\d+/", $out[1] . ' ' . $out[2], $matches)) {
            return false;
        }

        $titles = reset($titleMatches);
        $titles[] = 'swap_' . $titles[0];
        $titles[] = 'swap_' . $titles[1];
        $titles[] = 'swap_' . $titles[2];

        $string = '';

        array_walk(
            array_combine($titles, reset($matches)),
            function ($item, $key) use (&$string) {
                $string .= ($string ? ' ' : '') . $key . ': ' . $item;
            }
        );

        return $string;
    }
}
