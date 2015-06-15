<?php

namespace app\classes;

use app\classes\Utils;

class Wordifier
{
    private static $curBig=array('USD'=>array('доллар США','доллара США','долларов США'),'RUB'=>array('рубль','рубля','рублей'));
    private static $curSmall=array('USD'=>array('цент','цента','центов'),'RUB'=>array('копейка','копейки','копеек'));
    private static $num10 = array("","один ","два ","три ","четыре ","пять ","шесть ","семь ","восемь ","девять ",);
    private static $num10x = array("","одна ","две ","три ","четыре ","пять ","шесть ","семь ","восемь ","девять ",);
    private static $num20 = array("десять ","одиннадцать ","двенадцать ","тринадцать ","четырнадцать ","пятнадцать ","шестнадцать ","семнадцать ","восемнадцать ","девятнадцать ");
    private static $num100 = array("","","двадцать ","тридцать ","сорок ","пятьдесят ","шестьдесят ","семьдесят ","восемьдесят ","девяносто ");
    private static $num1000 = array("","сто ","двести ","триста ","четыреста ","пятьсот ","шестьсот ","семьсот ","восемьсот ","девятьсот " );
    private static $sections = array(array(' ',' ',' '),array('тысяча','тысячи','тысяч'),array("миллион","миллиона","миллионов"),array("миллиард","миллиарда","миллиардов"));

    private static function MakeSections($num, $sect)
    {
        if ($num >= 1000) {
            $s = self::MakeSections(floor($num/1000), $sect+1) . ' ';
            $num=$num%1000;
        }
        else
            $s='';

        $s .= self::$num1000[ floor($num/100) ];
        $num = $num % 100;

        if ($num >= 10 && $num <= 19) {
            $s .= self::$num20[ $num-10 ];
        }
        else {
            $s .= self::$num100[ floor($num/10) ];
            $num = $num % 10;
            if ($sect == 1)
                $s .= self::$num10x[$num];
            else
                $s .= self::$num10[$num];
        }
        $s .= Utils::rus_plural($num, self::$sections[$sect][0], self::$sections[$sect][1], self::$sections[$sect][2]);
        if ($sect == 0)
            return array($s, $num);
        else
            return $s;
    }

    public static function Make($num, $currency){
        $num = round($num, 2);

        $isMinus = false;

        if ($num < 0) {
            $num = abs($num);
            $isMinus = true;
        }


        if (floor($num) == 0)
            $v = array('ноль ', 0);
        else
            $v = self::MakeSections(floor($num), 0);
        $s = $v[0];

        if ($isMinus)
            $s = 'минус ' . $s;

        $s = strtr(mb_substr($s, 0, 1, 'utf-8'), 'мнодтчпшсв', 'МНОДТЧПШСВ') . mb_substr($s, 1, -1, 'utf-8');
        //$s=mb_strtoupper(mb_substr($s,0,1)).mb_substr($s,1);
        //$s=mb_strtoupper(mb_substr($s,0,1,'utf-8'),'utf-8').mb_substr($s,1,-1,'utf-8');
        $s .= Utils::rus_plural($v[1], self::$curBig[$currency][0], self::$curBig[$currency][1], self::$curBig[$currency][2]);
        $c = round(($num-floor($num))*100);
        $s .= ' ' . sprintf('%02d', $c) . ' ' . Utils::rus_plural($c, self::$curSmall[$currency][0], self::$curSmall[$currency][1], self::$curSmall[$currency][2]);

        return $s;
    }
}