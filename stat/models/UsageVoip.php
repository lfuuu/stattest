<?php

class UsageVoip extends ActiveRecord\Model
{
    static $table_name = "usage_voip";
    static $private_key = 'id';
    
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
    
    /**
     *  Возвращает информацию о номере, на который осуществился переход, если такой существует
     *  @param int $usage_id  ID номера в usage_voip
     */
    public static function checkNumberWasMoved($usage_id)
    {
        $check_move = null;
        if ($usage_id && UsageVoip::exists($usage_id))
        {
            $current = UsageVoip::find($usage_id);
            $options = array();
            $options['select'] = '*,UNIX_TIMESTAMP(actual_from) as from_ts,UNIX_TIMESTAMP(actual_to) as to_ts';
            $options['conditions'] = array(
                'e164 = ? AND id <> ? AND actual_from = DATE_ADD(?, INTERVAL 1 DAY) AND is_moved = ?', 
                $current->e164, 
                $current->id, 
                $current->actual_to,
                1
            );
            $check_move = UsageVoip::first($options);
        }
        return $check_move;
    }
    
    /**
     * @param str $from_client  клиент с которого перенесен номер
     * @param str $to_client  клиент на которого перенесен номера
     * @param str $actual_from  дата начала работы номера, на который был осущевствлен переход
     */
    public static function getMovedNumber($from_client, $to_client, $actual_from)
    {
        $actual_from = date('Y-m-d', strtotime($actual_from));
        
        $options = array();
        $options['select'] = '  a.E164 as number, 
                                a.id as from_id,
                                b.id as to_id,
                                a.client as from_client,
                                b.client as to_client';
        $options['from'] = 'usage_voip as a';
        $options['joins'] = 'LEFT JOIN usage_voip as b ON (b.E164 = a.E164)';
        $options['conditions'] = array(
            '   a.id <> b.id 
            AND CAST(NOW() as DATE) <= a.actual_to 
            AND a.actual_to < "2029-01-01" 
            AND a.actual_to = DATE_SUB(b.actual_from, INTERVAL 1 DAY) 
            AND a.client = ? 
            AND b.client = ? 
            AND b.actual_from = ? 
            AND b.is_moved = ?  
            AND b.is_moved_with_pbx = ?',
            $from_client,
            $to_client,
            $actual_from,
            1,
            1
        );
        $data = UsageVoip::find('all', $options);
        return $data;
    }
}
