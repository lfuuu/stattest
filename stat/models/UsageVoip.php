<?php

class UsageVoip extends ActiveRecord\Model
{
    static $table_name = "usage_voip";
    static $private_key = 'id';

    static $allowedDirection = array(
        "full"     => "Все",
        "russia"   => "Россия",
        "localmob" => "Местные стац.+моб.",
        "local"    => "Местные стац.",
        "blocked"  => "Запрет исх. связи"
    );
    
     /**
     *  Возвращает информацию о номере, с которого осуществился переход, если такой существует
     *  @param string $number  номер телефона
     *  @param string $actual_from  дата с которой телефон является активным
     */
    public static function checkNumberIsMoved($number, $actual_from)
    {
        $check_move = null;
        
        $actual_from = date('Y-m-d', strtotime($actual_from));
        
        $options = array();
        $options['select'] = '*,UNIX_TIMESTAMP(actual_from) as from_ts,UNIX_TIMESTAMP(actual_to) as to_ts';
        $options['conditions'] = array(
            'e164 = ? AND  actual_to = DATE_SUB(?, INTERVAL 1 DAY)', 
            $number, 
            $actual_from
        );
        $check_move = UsageVoip::first($options);
        
        return $check_move;
    }

}
