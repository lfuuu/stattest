<?php

class OnlimeDeliveryLimit
{
    static $limit_after = 35; // after border time 
    static $limit_before = 35; // before border time
    static $border_time = "16:00:00";

    static $cache = null;

    public function checkOnDate($date, $time)
    {
        if(is_null(self::$cache) || !isset(self::$cache[$date]))
            self::_loadCacheOnDate($date);

        //echo "\n---------------------\n".$date." ".$time;
        //print_r(self::$cache);
        return self::addValue($date, $time);

    }

    private function _loadCacheOnDate($date)
    {
        $borderTime = $date." ".self::$border_time;

        $r = OnlimeDelivery::find("first",
                array(
                    "select" => "sum(if(delivery_date < ?, 1,0)) count_before, sum(if(delivery_date >= ?, 1,0)) count_after",
                    "conditions" => array(
                        "delivery_date between ? and ?", $borderTime, $borderTime, $date." 00:00:00", $date." 23:59:59"
                        )
                    )
                );

        if($r)
        {
            self::$cache[$date] = array("before" => $r->count_before, "after" => $r->count_after);
        }else{
            self::$cache[$date] = array("before" => 0, "after" => 0);
        }
    }

    public function addValue($date, $time)
    {
        $isBefore = strtotime($date." ".$time) < strtotime($date." ".self::$border_time);

        $c = &self::$cache[$date][$isBefore ? "before" : "after"];

        if($isBefore)
        {
            if($c+1 <= self::$limit_before)
            {
                $c++;
                return true;
            }
            throw new Exception("Превышен лимит завявок на ".$date.", на период до ".self::$border_time." (заявок: ".self::$limit_before.")");
        }else{
            if($c+1 <= self::$limit_after)
            {
                $c++;
                return true;
            }
            throw new Exception("Превышен лимит завявок на ".$date.", на период после ".self::$border_time." (заявок: ".self::$limit_after.")");
        }
        return false;

    }



}

