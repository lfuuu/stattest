<?php

namespace app\classes\partners\rewards;

use app\models\BillLine;
use app\models\PartnerRewards;

interface Reward
{

    /**
     * @return string
     */
    public static function getField();

    /**
     * @param PartnerRewards $reward
     * @param BillLine $line
     * @param array $settings
     */
    public static function calculate(PartnerRewards $reward, BillLine $line, array $settings);

}