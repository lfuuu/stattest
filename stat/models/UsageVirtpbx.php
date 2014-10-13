<?php

class UsageVirtpbx extends ActiveRecord\Model
{
    static $table_name = "usage_virtpbx";
    static $private_key = 'id';
    
    /**
     *  Возвращает информацию о АТС, с которой осуществился переход, если такая существует
     *  @param string $actual_from  дата с которой АТС является активной
     */
    public static function checkVpbxIsMoved($actual_from)
    {
        $check_move = null;
        
        $actual_from = date('Y-m-d', strtotime($actual_from));

        $options = array();
        $options['select'] = '*,UNIX_TIMESTAMP(actual_from) as from_ts,UNIX_TIMESTAMP(actual_to) as to_ts';
        $options['conditions'] = array(
                'actual_to = DATE_SUB(?, INTERVAL 1 DAY)', 
                $actual_from
        );
        $check_move = UsageVirtpbx::first($options);
        
        return $check_move;
    }
    
    /**
     * Возвращает информацию о АТС, на которую осуществился переход, если такая существует
     * @param int $usage_id  ID АТС 
     */
    public static function checkVpbxWasMoved($usage_id)
    {
        $check_move = null;
        if ($usage_id && UsageVirtpbx::exists($usage_id))
        {
            $current = UsageVirtpbx::find($usage_id);
            $options = array();
            $options['select'] = '*,UNIX_TIMESTAMP(actual_from) as from_ts,UNIX_TIMESTAMP(actual_to) as to_ts';
            $options['conditions'] = array(
                    'actual_from = DATE_ADD(?, INTERVAL 1 DAY) AND is_moved = ?', 
                    $current->actual_to,
                    1
            );
            $check_move = UsageVirtpbx::first($options);
        }
        return $check_move;
    }
    
    /**
     * Возвращает информацию о перенесенной АТС, вместе с которой был перенесен номер
     * @param str $from_client  клиент с которого перенесен номер
     * @param str $to_client  клиент на которого перенесен номера
     * @param str $actual_from  дата начала работы номера, на который был осущевствлен переход
     */
    public static function checkNumberIsMovedWithPbx($from_client, $to_client, $actual_from)
    {
        $actual_from = date('Y-m-d', strtotime($actual_from));
    
        $check_move = null;
        $options = array();
        $options['select'] = 'b.id';
        $options['from'] = 'usage_virtpbx as a';
        $options['joins'] = 'LEFT JOIN usage_virtpbx as b ON (a.actual_to = DATE_SUB(b.actual_from, INTERVAL 1 DAY))';
        $options['conditions'] = array(
                '   a.id <> b.id 
                AND a.actual_to < "2029-01-01" 
                AND CAST(NOW() as DATE) <= a.actual_to
                AND a.client = ? 
                AND b.client = ? 
                AND b.actual_from = ? 
                AND b.is_moved = ?',
                $from_client,
                $to_client,
                $actual_from,
                1
        );
        $check_move = UsageVirtpbx::first($options);
        return $check_move;
    }
}
