<?php

namespace app\health;
use app\models\Region;


/**
 * ЛС со включенными номерами, не выгружаемые в СОРМ. Регион - Краснодар.
 */
class MonitorSormClientsReg97 extends MonitorSormClients
{
    public $regionId = Region::KRASNODAR;

    public function getValue()
    {
        return $this->_getValue($this->regionId);
    }
}