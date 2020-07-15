<?php

namespace app\modules\nnp2\commands;

use app\modules\nnp\models\Country;
use app\modules\nnp2\models\NumberRange;
use yii\base\InvalidParamException;
use yii\console\Controller;

class NumberRangeController extends Controller
{
    const CHUNK_SIZE_DELETE = 100;

    /**
     * @param $message
     * @param bool $lineBreak
     */
    protected function logLine($message, $lineBreak = true)
    {
        echo date("d-m-Y H:i:s") . ": " . $message . ($lineBreak ? PHP_EOL : '');
    }

    /**
     * Удаление диапазонов без гео
     *
     */
    public function actionRemoveEmpty()
    {
        $time0 = microtime(true);
        echo PHP_EOL;
        $this->logLine('Started.');

        // number ranges
        $nrQuery = NumberRange::find()
            ->select('id')
            ->where(['IS', 'geo_place_id', null]);

        $all = $nrQuery->count();
        $nrIds = $nrQuery->column();

        $i = 0;
        if ($nrQuery) {
            foreach (array_chunk($nrIds, self::CHUNK_SIZE_DELETE) as $chunk) {
                $max = min($i + self::CHUNK_SIZE_DELETE, $all);
                $this->logLine(sprintf("Removing %s - %s of %s...", $i + 1, $max, $all));

                NumberRange::deleteAll(['id' => $chunk]);

                $i = $max;
            }
        }

        $this->logLine('Done in ' . round(microtime(true) - $time0, 2).' sec');
    }
}
