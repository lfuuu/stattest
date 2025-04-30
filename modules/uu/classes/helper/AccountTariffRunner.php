<?php

namespace app\modules\uu\classes\helper;


use app\classes\helpers\CalculateGrowthRate;
use app\modules\uu\models\AccountTariff;

class AccountTariffRunner
{
    private ?int $maxId = null;
    private ?int $stepLen = null;
    private ?CalculateGrowthRate $rater = null;

    public function __construct($stepLen = 10000)
    {
        $this->maxId = AccountTariff::find()->max('id');
        $this->stepLen = $stepLen;
        $this->rater = new CalculateGrowthRate();
    }

    public function run(\Closure $cb)
    {
        echo PHP_EOL;

        for ($i = 0; $i <= $this->maxId + $this->stepLen; $i += $this->stepLen) {
            echo "\r[ " . str_pad($i . ' / ' . $this->maxId . ' => ' . round($i / ($this->maxId / 100)) . '% ', 30, '.') . '] ';
            echo sprintf('%20s', number_format($this->rater->calculate($i))) . ' per sec  ';
            $cb(($i + 1), ($i + $this->stepLen));
        }
    }
}