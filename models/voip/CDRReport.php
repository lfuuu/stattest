<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 28.11.2016
 * Time: 10:18
 */

namespace app\models\voip;

use \Exception;
use Yii;
use yii\base\Model;
use yii\db\Connection;
use yii\db\Command;

class CDRReportException extends Exception { }

class CDRReport extends Model
{
    public $server_id = null;
    public $connect_time_start = null;
    public $connect_time_end = null;
    public $session_time_start = null;
    public $session_time_end = null;
    public $is_full_way = null;
    public $is_success_calls = null;
    public $src_route_id = null;
    public $dst_route_id = null;
    public $src_route = null;
    public $dst_route = null;
    public $src_number_mask = null;
    public $dst_number_mask = null;
    public $src_operator_id = null;
    public $dst_operator_id = null;
    public $src_region_id = null;
    public $dst_region_id = null;
    public $src_contract_id = null;
    public $dst_contract_id = null;

    public function rules ()
    {
        return [
            [['server_id', 'session_time_start', 'session_time_end', 'is_full_way', 'is_success_calls', 'src_route_id', 'dst_route_id', 'src_operator_id', 'dst_operator_id', 'src_region_id', 'dst_region_id'], 'integer'],
            [['connect_time_start', 'connect_time_end', 'src_number_mask', 'dst_number_mask', 'src_route', 'dst_route'], 'string']
        ];
    }

    public static function getServers () {
        return Yii::$app->dbPgSlave->createCommand("SELECT id, name FROM server")->queryAll();
    }

    public static function getTrunks ()
    {
        return Yii::$app->dbPgSlave->createCommand("
            SELECT 
              t.id,
             (CASE 
                    WHEN
                        st.id IS NOT NULL
                    THEN
                        '(' || st.id || ') ' || t.name
                    ELSE
                        t.name
                END) AS name,
                t.name AS native_name
            FROM 
                auth.trunk AS t 
            LEFT JOIN 
                billing.service_trunk AS st 
                ON
                    st.trunk_id = t.id
            ORDER BY
                st.id")->queryAll();
    }

    public static function getContracts ()
    {
        return Yii::$app->dbPgSlave->createCommand("
            SELECT
              st.contract_id AS id,
             (CASE	
                    WHEN
                        cct.name IS NOT NULL
                    THEN
                        st.contract_number || ' (' || cct.name || ')'
                    ELSE
                        st.contract_number
                END) AS name
            FROM
                billing.service_trunk AS st
            LEFT JOIN
                client_contract_type AS cct
                ON
                    cct.id = st.contract_type_id
            ORDER BY
                st.contract_number")->queryAll();
    }

    public static function getRegions ()
    {
        return Yii::$app->dbPgSlave->createCommand("
                SELECT 
                    id, 
                    name 
                FROM 
                    nnp.region")->queryAll();
    }

    public static function getOperators ()
    {
        return Yii::$app->dbPgSlave->createCommand("
                SELECT 
                    id, 
                    name 
                FROM 
                    nnp.operator")->queryAll();
    }

    public function getReport1 ()
    {
        $condition1 = '';
        $condition2 = '';
        $params = [];
        if ($this->server_id)
        {
            $condition1 = 'cc.server_id = :server_id';
            $condition2 = 'cr.server_id = :server_id';
            $params['server_id'] = $this->server_id;
        }
        if ($this->connect_time_start || $this->connect_time_end)
        {
            if ($this->connect_time_start)
            {
                $condition1 .= ' AND cc.connect_time BETWEEN :connect_time_start AND ';
                $condition2 .= ' AND cr.connect_time BETWEEN :connect_time_start AND ';
                $params['connect_time_start'] = $this->connect_time_start;
            }
            else
            {
                $condition1 .= ' AND cc.connect_time BETWEEN to_timestamp(0) AND ';
                $condition1 .= ' AND cr.connect_time BETWEEN to_timestamp(0) AND ';
            }
            if ($this->connect_time_end)
            {
                $condition1 .= ':connect_time_end';
                $condition2 .= ':connect_time_end';
                $params['connect_time_end'] = $this->connect_time_end;
            }
            else
            {
                $condition1 .= 'now()';
                $condition2 .= 'now()';

            }
        }
        if ($this->session_time_start || $this->session_time_end)
        {
            if ($this->session_time_start)
            {
                $condition1 .= ' AND cc.session_time BETWEEN :session_time_start AND ';
                $params['session_time_start'] = $this->session_time_start;
            }
            else
            {
                $condition1 .= ' AND cc.session_time BETWEEN 0 AND ';
            }
            if ($this->session_time_end)
            {
                $condition1 .= ':session_time_end';
                $params['session_time_end'] = $this->session_time_end;
            }
            else
            {
                $condition1 .= '2592000';
            }
        }
        if ($this->src_route)
        {
            $condition1 .= ' AND cc.src_route = :src_route';
            $params['src_route'] = $this->src_route;
        }
        if ($this->dst_route)
        {
            $condition1 .= ' AND cc.dst_route = :dst_route';
            $params['dst_route'] = $this->dst_route;
        }
        if ($this->src_route_id || $this->src_operator_id || $this->src_region_id || $this->dst_route_id || $this->dst_operator_id || $this->dst_region_id)
        {
            $condition2 .= 'AND ((cr.orig = true';
            if ($this->src_route_id)
            {
                $condition2 .= ' AND st.id = :src_route_id';
                $params['src_route_id'] = $this->src_route_id;
            }
            if ($this->src_operator_id)
            {
                $condition2 .= ' AND cr.nnp_operator_id = :src_operator_id';
                $params['src_operator_id'] = $this->src_operator_id;
            }
            if ($this->src_region_id)
            {
                $condition2 .= ' AND cr.nnp_region_id = :src_region_id';
                $params['src_region_id'] = $this->src_region_id;
            }
            $condition2 .= ')';
            $condition2 .= 'OR (cr.orig = false';
            if ($this->dst_route_id)
            {
                $condition2 .= ' AND st.id = :dst_route_id';
                $params['dst_route_id'] = $this->dst_route_id;
            }
            if ($this->dst_operator_id)
            {
                $condition2 .= ' AND cr.nnp_operator_id = :dst_operator_id';
                $params['dst_operator_id'] = $this->dst_operator_id;
            }
            if ($this->dst_region_id)
            {
                $condition2 .= ' AND cr.nnp_region_id = :dst_region_id';
                $params['dst_region_id'] = $this->dst_region_id;
            }
            $condition2 .= '))';
        }
        if ($this->is_success_calls)
        {
            $condition1 .= ' AND (session_time > 0 OR disconnect_cause IN (16,17,18,19,21,31))';
        }
        if ($this->src_number_mask)
        {
            $condition1 .= ' AND cc.src_number LIKE :src_number_mask || \'%\'';
            $condition2 .= ' AND cr.src_number::varchar LIKE :src_number_mask || \'%\'';
            $params['src_number_mask'] = $this->src_number_mask;
        }
        if ($this->dst_number_mask)
        {
            $condition1 .= ' AND cc.dct_number LIKE :dst_number_mask || \'%\'';
            $condition2 .= ' AND cr.dct_number::varchar LIKE :dst_number_mask || \'%\'';
            $params['dst_number_mask'] = $this->dst_number_mask;
        }
        if ($condition1)
        {
            if (strpos($condition1, ' AND') === 0)
            {
                $condition1 = substr($condition1, 0, 4);
            }
            $condition1 = 'WHERE ' . $condition1;
        }
        if ($condition2)
        {
            if (strpos($condition1, ' AND') === 0)
            {
                $condition2 = substr($condition2, 0, 4);
            }
            $condition2 = 'WHERE ' . $condition2;
        }

        $sql = "WITH cc AS (
                    SELECT
                        cc.id,
                        cc.call_id,
                        date_trunc('second', cc.setup_time) AS setup_time,
                        cc.session_time,
                        cc.disconnect_cause,
                        cc.src_number AS raw_src_number,
                        cc.redirect_number,
                        cc.releasing_party,
                        cc.src_route,
                        cc.dst_route,
                        date_trunc('second', cc.connect_time - cc.setup_time) AS pdd
                    FROM
                        calls_cdr.cdr AS cc
                    $condition1),
                    
                    cr AS (
                    SELECT
                        cr.cdr_id,
                        cr.orig,
                        o.name AS operator_name,
                        r.name AS region_name,
                        cr.src_number,
                        cr.dst_number,
                        st.id AS contract_id,
						st.contract_number
                    FROM
                        calls_raw.calls_raw AS cr
                    LEFT JOIN
                        nnp.\"operator\" AS o
                        ON
                            cr.nnp_operator_id = o.\"id\"
                    LEFT JOIN
                        nnp.region AS r
                        ON
                            r.\"id\" = cr.nnp_region_id
                    LEFT JOIN
                        billing.service_trunk AS st
                        ON
                            st.trunk_id = cr.trunk_id
                    $condition2)
                    
                    SELECT 
                        cc.*,
                        cr1.operator_name AS operator_name_a,
                        cr2.operator_name AS operator_name_b,
                        cr1.region_name AS region_name_a,
                        cr2.region_name AS region_name_b,
                        cr1.contract_number AS contract_name_a,
						cr2.contract_number AS contract_name_b
                    FROM 
                      cc
                    LEFT JOIN
                        cr AS cr1
                        ON
                            cc.id = cr1.cdr_id
                                AND
                            cr1.orig = true
                    LEFT JOIN
                        cr AS cr2
                        ON
                            cc.id = cr1.cdr_id
                                AND
                            cr1.orig = false
                    ORDER BY
					  cc.id
                    LIMIT
                      1000";

        $result = Yii::$app->dbPgSlave->createCommand($sql, $params)->queryAll();

        return $result;
    }
}