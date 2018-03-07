<?php

namespace app\health;
use app\models\Region;


/**
 * ЛС со включенными номерами, не выгружаемые в СОРМ. Регион - Нижный Новгород.
 */
class MonitorSormClientsReg88 extends MonitorSormClients
{
    public $regionId = Region::NIZHNY_NOVGOROD;

    public function getValue()
    {
        return $this->_getValue($this->regionId);
    }
}