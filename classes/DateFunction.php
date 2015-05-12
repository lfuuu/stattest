<?php

namespace app\classes;

class DateFunction
{
    public static function dateReplaceMonth($string,$nMonth){
        $p=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
        $string=str_replace('месяца',$p[$nMonth-1],$string);
        $p=array('январе','феврале','марте','апреле','мае','июне','июле','августе','сентябре','октябре','ноябре','декабре');
        $string=str_replace('месяце',$p[$nMonth-1],$string);
        $p=array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');
        $string=str_replace('Месяц',$p[$nMonth-1],$string);
        $p=array('январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь');
        $string=str_replace('месяц',$p[$nMonth-1],$string);
        return $string;
    }

    public static function mdate($ts, $format){
        if ($ts) $s=date($format,$ts); else $s=date($format);
        if ($ts) $d=getdate($ts); else $d=getdate();

        return self::dateReplaceMonth($s, $d['mon']);
    }
}
