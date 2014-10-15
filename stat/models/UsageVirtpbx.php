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
            $options = array();
            $options['from'] = 'usage_virtpbx as a';
            $options['select'] = 
                      'a.*,'
                    . 'c.id as client_id';
            $options['joins'] = 'LEFT JOIN clients as c ON (c.client = a.client)';
            $options['conditions'] = 'a.id = ' . $usage_id;
            $current = UsageVirtpbx::first($options);
            $options = array();
            $options['select'] = 
                      '*,'
                    . 'UNIX_TIMESTAMP(actual_from) as from_ts,'
                    . 'UNIX_TIMESTAMP(actual_to) as to_ts';
            $options['conditions'] = array(
                    '   actual_from = DATE_ADD(?, INTERVAL 1 DAY) 
                    AND is_moved = ? 
                    AND moved_from = ?', 
                    $current->actual_to,
                    1,
                    $current->client_id
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
        $options['joins'] = 'LEFT JOIN usage_virtpbx as b ON (a.actual_to = DATE_SUB(b.actual_from, INTERVAL 1 DAY))'
                . ' LEFT JOIN clients as c ON (a.client = c.client)';
        $options['conditions'] = array(
                '   a.id <> b.id 
                AND a.actual_to < "2029-01-01" 
                AND CAST(NOW() as DATE) <= a.actual_to
                AND a.client = ? 
                AND b.client = ? 
                AND b.actual_from = ? 
                AND b.moved_from = c.id  
                AND b.is_moved = ?',
                $from_client,
                $to_client,
                $actual_from,
                1
        );
        $check_move = UsageVirtpbx::first($options);
        return $check_move;
    }
    public static function getAllPosibleMovedPbx($actual_from, $client)
    {
        $check_move = null;
        $data = array();
        $actual_from = date('Y-m-d', strtotime($actual_from));

        $options = array();
        $options['select'] = 'a.*,c.id as client_id, UNIX_TIMESTAMP(a.actual_from) as from_ts,UNIX_TIMESTAMP(a.actual_to) as to_ts';
        $options['from'] = 'usage_virtpbx as a';
        $options['joins'] = 'LEFT JOIN clients as c ON (a.client = c.client)' . 
                'LEFT JOIN usage_virtpbx as b ON (c.id = b.moved_from) ';
        $options['conditions'] = array(
                '       a.actual_to = DATE_SUB(?, INTERVAL 1 DAY) 
                    AND a.client <> ? 
                    AND (ISNULL(b.actual_from) OR b.actual_from <> ? OR b.client = ?)', 
                    $actual_from,
                    $client,
                    $actual_from,
                    $client
        );
        $check_move = UsageVirtpbx::all($options);
        if (!empty($check_move))
        {
            foreach ($check_move as $v)
            {
                $data[$v->client_id] = $v->client;
            }
        }
        if (count($data)>1)
        {
            $data[''] = '-- Не выбранно --';
            ksort($data);
        }

        return $data;
    }
}
