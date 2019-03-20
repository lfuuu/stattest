<?php

namespace app\health;

use app\models\Region;

/**
 * Используемые номера в регионе сормирования, и не выгружаемые
 */
class MonitorSorm_Krasnoiarsk extends MonitorSormItGrad
{
    protected $region_id = Region::KRASNOIARSK;
}