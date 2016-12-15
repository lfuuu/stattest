<?php
/**
 * CDR report model
 */

namespace app\models\voip\filter;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use app\classes\yii\McnSqlDataProvider;
use yii\db\Expression;
use app\models\billing\DisconnectCause;

/**
 * Class Cdr
 * @package app\models\voip\filter
 *
 * @property int $server_id
 * @property string $setup_time_from
 * @property string $setup_time_to
 * @property string $session_time_from
 * @property string $session_time_to
 * @property bool $is_full_way
 * @property bool $is_success_calls
 * @property bool $src_route
 * @property bool $dst_route
 */
class Cdr extends Model
{
    const UNATTAINABLE_SESSION_TIME = 2592000;

    public $server_id = null;
    public $setup_time_from = null;
    public $setup_time_to = null;
    public $session_time_from = null;
    public $session_time_to = null;
    public $is_full_way = null;
    public $is_success_calls = null;
    public $src_route = null;
    public $dst_route = null;
    public $src_number = null;
    public $dst_number = null;
    public $src_operator_id = null;
    public $dst_operator_id = null;
    public $src_region_id = null;
    public $dst_region_id = null;
    public $src_contract_id = null;
    public $dst_contract_id = null;

    public $releasing_party = null;
    public $call_id = null;
    public $disconnect_cause = null;
    public $redirect_number = null;

    public function rules()
    {
        return [
            [
                [
                    'server_id',
                    'session_time_from',
                    'session_time_to',
                    'is_full_way',
                    'is_success_calls',
                    'src_operator_id',
                    'dst_operator_id',
                    'src_region_id',
                    'dst_region_id',
                    'src_contract_id',
                    'dst_contract_id',
                    'releasing_party',
                    'call_id',
                    'disconnect_cause',
                    'session_time',
                ],
                'integer'
            ],
            [
                [
                    'setup_time_from',
                    'setup_time_to',
                    'src_number',
                    'dst_number',
                    'dst_route',
                    'src_route',
                    'redirect_number'
                ],
                'string'
            ]
        ];
    }

    public function getSuccessDisconnectCauses ()
    {
        return implode(',', DisconnectCause::$successCodes);
    }

    public function getReport()
    {
        $condition1 = $condition2 = $condition3 = $params = [];

        if ($this->server_id) {
            $condition1[] = 'cc.server_id = :server_id';
            $condition2[] = 'cr.server_id = :server_id';
            $params[':server_id'] = $this->server_id;
        }

        if ($this->setup_time_from || $this->setup_time_to) {
            $condition1[] = 'cc.setup_time BETWEEN :setup_time_from AND :setup_time_to';
            $params[':setup_time_from'] = $this->setup_time_from ? $this->setup_time_from : new Expression('to_timestamp(0)');
            $params[':setup_time_to'] = $this->setup_time_to ? $this->setup_time_to : new Expression('now()');
        }

        if ($this->session_time_from || $this->session_time_to) {
            $condition1[] = 'cc.session_time BETWEEN :session_time_from AND :session_time_to';
            $params[':session_time_from'] = $this->session_time_from ? $this->session_time_from : 0;
            $params[':session_time_to'] = $this->session_time_to ? $this->session_time_to : self::UNATTAINABLE_SESSION_TIME;
        }

        if ($this->src_route) {
            $condition1[] = 'cc.src_route = :src_route';
            $params[':src_route'] = $this->src_route;
        }

        if ($this->dst_route) {
            $condition1[] = 'cc.dst_route = :dst_route';
            $params[':dst_route'] = $this->dst_route;
        }

        if ($this->src_operator_id || $this->src_region_id) {
            if ($this->src_operator_id) {
                $condition3[] = 'cr1.nnp_operator_id = :src_operator_id';
                $params[':src_operator_id'] = $this->src_operator_id;
            }
            if ($this->src_region_id) {
                $condition3[] = 'cr1.nnp_region_id = :src_region_id';
                $params[':src_region_id'] = $this->src_region_id;
            }
        }

        if ($this->dst_operator_id || $this->dst_region_id) {
            if ($this->dst_operator_id) {
                $condition3[] = 'cr2.nnp_operator_id = :dst_operator_id';
                $params[':dst_operator_id'] = $this->dst_operator_id;
            }
            if ($this->dst_region_id) {
                $condition3[] = 'cr2.nnp_region_id = :dst_region_id';
                $params[':dst_region_id'] = $this->dst_region_id;
            }
        }

        if ($this->is_success_calls) {
            $condition1[] = '(session_time > 0 OR disconnect_cause = ANY(:success_causes::int[]))';
            $params[':success_causes'] = $this->getSuccessDisconnectCauses();
        }

        if ($this->src_number) {
            $condition1[] = 'cc.src_number LIKE :src_number || \'%\'';
            $condition2[] = 'cr.src_number::varchar LIKE :src_number || \'%\'';
            $params[':src_number'] = $this->src_number;
        }

        if ($this->dst_number) {
            $condition1[] = 'cc.dst_number LIKE :dst_number || \'%\'';
            $condition2[] = 'cr.dst_number::varchar LIKE :dst_number || \'%\'';
            $params[':dst_number'] = $this->dst_number;
        }

        if ($this->disconnect_cause) {
            $condition1[] = 'cc.disconnect_cause = :disconnect_cause';
            $condition2[] = 'cr.disconnect_cause = :disconnect_cause';
            $params[':disconnect_cause'] = $this->disconnect_cause;
        }

        if ($this->releasing_party) {
            $condition1[] = 'cc.releasing_party = :releasing_party';
            $params[':releasing_party'] = $this->releasing_party;
        }

        if ($this->call_id) {
            $condition1[] = 'cc.call_id = :call_id';
            $params[':call_id'] = $this->call_id;
        }

        $condition1 = $condition1 ? 'WHERE ' . implode(' AND ', $condition1) : '';
        $condition2 = $condition2 ? 'WHERE ' . implode(' AND ', $condition2) : '';
        $condition3 = $condition3 ? 'WHERE ' . implode(' AND ', $condition3) : '';

        if (!$condition1 && !$condition2 && !$condition3) {
            return new ArrayDataProvider([
                'allModels' => [],
            ]);
        }

        $sql = "WITH cc AS (
                    SELECT
                        cc.id,
                        cc.call_id,
                        date_trunc('second', cc.setup_time) AS setup_time,
                        cc.session_time,
                        cc.disconnect_cause,
                        cc.redirect_number,
                        cc.releasing_party,
                        cc.src_route,
                        cc.dst_route,
                        cc.src_number,
                        cc.dst_number,
                        date_trunc('second', cc.connect_time) AS connect_time,
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
                        st.id AS contract_id,
						st.contract_number,
						cct.name AS contract_type,
						cr.nnp_operator_id,
						cr.nnp_region_id
                    FROM
                        calls_raw.calls_raw AS cr
                    LEFT JOIN
                        nnp.operator AS o
                        ON
                            cr.nnp_operator_id = o.id
                    LEFT JOIN
                        nnp.region AS r
                        ON
                            r.id = cr.nnp_region_id
                    LEFT JOIN
                        billing.service_trunk AS st
                        ON
                            st.id = cr.trunk_id
                    LEFT JOIN
                        stat.client_contract_type AS cct
                        ON
                            cct.id = st.contract_type_id
                    $condition2)
                    
                    SELECT 
                        cc.*,
                        cr1.operator_name AS src_operator_name,
                        cr2.operator_name AS dst_operator_name,
                        cr1.region_name AS src_region_name,
                        cr2.region_name AS dst_region_name,
                        cr1.contract_number || ' (' || cr1.contract_type || ')' AS src_contract_name,
						cr2.contract_number || ' (' || cr2.contract_type || ')' AS dst_contract_name,
						cr1.nnp_operator_id,
						cr2.nnp_operator_id,
						cr1.nnp_region_id,
						cr2.nnp_region_id
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
                    $condition3";

        return new McnSqlDataProvider([
            'db' => 'dbPgSlave',
            'sql' => $sql,
            'params' => $params,
            'pagination' => [],
            'sort' => [
                'attributes' => [
                    'call_id',
                    'setup_time',
                    'session_time',
                    'disconnect_cause',
                    'src_number',
                    'src_operator_name',
                    'src_region_name',
                    'dst_number',
                    'dst_operator_name',
                    'dst_region_name',
                    'redirect_number',
                    'src_route',
                    'src_contract_name',
                    'dst_route',
                    'dst_contract_name',
                    'releasing_party',
                    'connect_time',
                    'pdd'
                ],
            ],
        ]);
    }
}