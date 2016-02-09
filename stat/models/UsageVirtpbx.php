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
