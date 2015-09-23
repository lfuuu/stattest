<?php
namespace app\classes\report;

use app\models\UsageVoip;
use app\models\VoipNumber;
use PDO;
use yii\db\Expression;
use Yii;

class LostCalls
{
    const CALL_MODE_OUTCOMING = 'outcoming';
    const CALL_MODE_INCOMING = 'incoming';
    const CALL_MODE_INCOMING8800 = 'incoming8800';

    public static $modes = [
        self::CALL_MODE_OUTCOMING => 'Исходящие',
        self::CALL_MODE_INCOMING => 'Входящие',
        self::CALL_MODE_INCOMING8800 => 'Входящие на 8800',
    ];

    private static $ourNumbers = [];

    public static function prepare($date, $region, $mode)
    {
        set_time_limit(300); //5*60 sec

        self::up();
        /* @var $pg Yii\db\Connection */
        $pg = Yii::$app->dbPg;
        $calls = $pg->createCommand('
            SELECT id, cdr_id, server_id AS region, connect_time, src_number, dst_number, billed_time, \''.$mode.'\' AS mode
                FROM "calls_raw"."calls_raw_' . date('Ym', strtotime($date)) . '"
                WHERE server_id = :region AND billed_time > 0 AND account_id IS NULL
                AND connect_time BETWEEN :dateFrom AND :dateTo'
            . ($mode == self::CALL_MODE_INCOMING8800 ? ' AND dst_number BETWEEN 78000000000 AND 78009999999' : '')
            , [':dateFrom' => $date . ' 00:00:00', ':dateTo' => $date . ' 23:59:59', ':region' => $region])
            ->queryAll(PDO::FETCH_ASSOC);
        $size = 0;
        $numbers = [];
        foreach ($calls as &$call) {
            if (self::isOurNumber($call, $mode, $date)) {
                $numbers[] = $call;
                $size++;
                if ($size >= 10000)
                    break;
                elseif ($size % 1000 === 0)
                    self::saveNumbers($numbers);
            }
            unset($call);
        }
        unset($calls);
        self::saveNumbers($numbers);
    }

    public static function getCount($date, $region, $mode)
    {
        return Yii::$app->db
            ->createCommand(
                'SELECT COUNT(*) FROM tmp_calls_raw WHERE region = :region AND DATE(connect_time) = :date AND mode = :mode',
                [':date' => $date, ':region' => $region, ':mode' => $mode]
            )->queryScalar();
    }

    private static function up()
    {
        Yii::$app->db->createCommand("
            CREATE TABLE IF NOT EXISTS`tmp_calls_raw` (
                `id` INT(10) UNSIGNED NOT NULL,
                `cdr_id` INT(10) UNSIGNED NOT NULL,
	            `region` TINYINT(3) UNSIGNED NOT NULL,
                `connect_time` TIMESTAMP NOT NULL,
                `src_number` BIGINT(20) UNSIGNED NOT NULL,
                `dst_number` BIGINT(20) UNSIGNED NOT NULL,
                `billed_time` SMALLINT(5) UNSIGNED NOT NULL,
	            `mode` ENUM('outcoming','incoming','incoming8800') NOT NULL,
                PRIMARY KEY (`id`),
                INDEX `connect_time` (`connect_time`),
                INDEX `src_number` (`src_number`)
            )
            ENGINE=MEMORY
            ;
        ")->execute();
        Yii::$app->db->createCommand("
            TRUNCATE `tmp_calls_raw`;
        ")->execute();
    }

    private static function isOurNumber($call, $mode = self::CALL_MODE_OUTCOMING, $date = false)
    {
        if (!self::$ourNumbers) {
            if ($mode == self::CALL_MODE_INCOMING8800)
                self::$ourNumbers = UsageVoip::find()
                    ->select('E164')
                    ->andWhere(new Expression("'$date' BETWEEN `actual_from` AND `actual_to`"))
                    ->andWhere(['type_id' => '7800'])
                    ->column();
            else
                self::$ourNumbers = VoipNumber::find()
                    ->select('number')
                    ->column();
        }
        return in_array($mode == self::CALL_MODE_OUTCOMING ? $call['src_number'] : $call['dst_number'], self::$ourNumbers);
    }

    private static function saveNumbers(&$numbers)
    {
        if ($numbers)
            Yii::$app->db->createCommand()->batchInsert('tmp_calls_raw', array_keys($numbers[0]), $numbers)->execute();
        $numbers = [];
    }


}
