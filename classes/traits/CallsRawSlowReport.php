<?php
/**
 * Формирование отчета на основе "сырых" данных
 */

namespace app\classes\traits;

use app\classes\yii\CTEQuery;
use app\models\Organization;
use Yii;
use yii\db\Expression;
use app\models\billing\DisconnectCause;
use yii\db\Query;

trait CallsRawSlowReport
{
    /**
     * @return CTEQuery
     */
    private function _getSlowReport()
    {
        $this->dbConn = Yii::$app->dbPgSlave;

        $query1 = new CTEQuery();
        $query2 = new CTEQuery();
        $query3 = new CTEQuery();
        $query4 = new CTEQuery();

        $query1
            ->select(
                [
                    'cr.cdr_id',
                    'cr.connect_time',
                    'cr.billed_time session_time',
                    'cr.disconnect_cause',
                    'src_number' => new Expression('cr.src_number::varchar'),
                    'dst_number' => new Expression('cr.dst_number::varchar'),
                    'cr.pdd',
                    't.name src_route',
                    'o.name dst_operator_name',
                    'nc.name_rus dst_country_name',
                    'r.name dst_region_name',
                    'ci.name dst_city_name',
                    'st.contract_number || \' (\' || cct.name || \')\' dst_contract_name',
                    'sale' => new Expression(self::getMoneyCalculateExpression('@(cr.cost)')),
                    'orig_rate' => new Expression(self::getMoneyCalculateExpression('cr.rate')),
                    'cr.server_id',
                ]
            )
            ->from('calls_raw.calls_raw cr')
            ->leftJoin('auth.trunk t', 't.id = cr.trunk_id')
            ->leftJoin('billing.service_trunk st', 'st.id = cr.trunk_service_id')
            ->leftJoin('stat.client_contract_type cct', 'cct.id = st.contract_type_id')
            ->leftJoin('nnp.operator o', 'o.id = cr.nnp_operator_id')
            ->leftJoin('nnp.country nc', 'nc.code = cr.nnp_country_code')
            ->leftJoin('nnp.region r', 'r.id = cr.nnp_region_id')
            ->leftJoin('nnp.city ci', 'ci.id = cr.nnp_city_id')
            ->leftJoin('billing.clients c', 'c.id = cr.account_id')
            ->leftJoin('billing.currency_rate rate', 'rate.currency::public.currencies = c.currency AND rate.date = now()::date')
            ->andWhere('cr.orig')
            ->orderBy('connect_time')
            ->limit(500);

        $query2->select(
            [
                'cr.cdr_id',
                'cr.connect_time',
                't.name dst_route',
                'o.name src_operator_name',
                'nc.name_rus src_country_name',
                'r.name src_region_name',
                'ci.name src_city_name',
                'st.contract_number || \' (\' || cct.name || \')\' src_contract_name',
                'cost_price' => new Expression(self::getMoneyCalculateExpression('cr.cost')),
                'term_rate' => new Expression(self::getMoneyCalculateExpression('cr.rate')),
                'cr.server_id',
            ]
        )
            ->from('calls_raw.calls_raw cr')
            ->leftJoin('auth.trunk t', 't.id = cr.trunk_id')
            ->leftJoin('billing.service_trunk st', 'st.id = cr.trunk_service_id')
            ->leftJoin('stat.client_contract_type cct', 'cct.id = st.contract_type_id')
            ->leftJoin('nnp.operator o', 'o.id = cr.nnp_operator_id')
            ->leftJoin('nnp.country nc', 'nc.code = cr.nnp_country_code')
            ->leftJoin('nnp.region r', 'r.id = cr.nnp_region_id')
            ->leftJoin('nnp.city ci', 'ci.id = cr.nnp_city_id')
            ->leftJoin('billing.clients c', 'c.id = cr.account_id')
            ->leftJoin('billing.currency_rate rate', 'rate.currency::public.currencies = c.currency AND rate.date = now()::date')
            ->andWhere('NOT cr.orig')
            ->orderBy('connect_time')
            ->limit(500);

        $null = new Expression('NULL');

        $query3->select(
            [
                'cdr_id' => 'cu.id',
                'date_trunc(\'second\', setup_time) connect_time',
                'session_time' => $null,
                'disconnect_cause',
                'src_number',
                'dst_number',
                'pdd' => $null,
                'dst_route',
                'dst_operator_name' => $null,
                'dst_country_name' => $null,
                'dst_region_name' => $null,
                'dst_city_name' => $null,
                'dst_contract_name' => $null,
                'sale' => $null,
                'orig_rate' => $null,
                'cu.server_id',
                'cdr_id1' => $null,
                'src_route',
                'src_operator_name' => $null,
                'src_country_name' => $null,
                'src_region_name' => $null,
                'src_city_name' => $null,
                'src_contract_name' => $null,
                'cost_price' => $null,
                'term_rate' => $null,
                'server_id1' => $null,
                'margin' => $null,
            ]
        )->from('calls_cdr.cdr_unfinished cu')
            ->orderBy('connect_time')
            ->limit(500);


        if ($this->server_ids) {
            $condition = ['cr.server_id' => $this->server_ids];
            $query1->andWhere($condition)
            && $query2->andWhere($condition)
            && $query3
            && $query3->andWhere(['cu.server_id' => $this->server_ids]);
        }

        if ($this->connect_time_from || $this->correct_connect_time_to) {
            $condition = function ($field) {
                return [
                    'BETWEEN',
                    $field,
                    $this->connect_time_from ? $this->connect_time_from : new Expression('to_timestamp(0)'),
                    $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()'),
                ];
            };
            $query1->andWhere($condition('cr.connect_time'));
            $query2->andWhere($condition('cr.connect_time'));
            $query3 && $query3->andWhere($condition('setup_time'));
        }

        $query3 = null;
        $query2->limit(-1)->orderBy([]);
        $query1->limit(-1)->orderBy([]);

        if ($this->src_trunk_group_ids) {
            $query1->innerJoin('auth.trunk_group_item tgi', 'tgi.trunk_id = t.id');
            $query1->andWhere(['tgi.trunk_group_id' => $this->src_trunk_group_ids]);
        }

        if ($this->dst_trunk_group_ids) {
            $query = (new Query())
                ->select('tgi2.trunk_id')
                ->distinct()
                ->from([
                    'tgi' => 'auth.trunk_group_item',
                    'ttr' => 'auth.trunk_trunk_rule',
                    'tgi2' => 'auth.trunk_group_item',
                ])
                ->where([
                    'tgi.trunk_group_id' => $this->dst_trunk_group_ids
                ])
                ->andWhere('ttr.trunk_id =  tgi.trunk_id')
                ->andWhere('tgi2.trunk_group_id = ttr.trunk_group_id');

            $query2->andWhere(['t.id' => $query]);
        }


        if ($this->is_exclude_internal_trunk_orig) {
            $query1->leftJoin('billing.service_trunk bst', 'cr.trunk_service_id = bst.id');
            $query1->leftJoin('billing.clients bc', 'bc.id = bst.client_account_id AND bc.organization_id = '. Organization::INTERNAL_OFFICE);
            $query1->andWhere(['bc.id' => null]);
        }

        if ($this->is_exclude_internal_trunk_term) {
            $query2->leftJoin('billing.service_trunk bst', 'cr.trunk_service_id = bst.id');
            $query2->leftJoin('billing.clients bc', 'bc.id = bst.client_account_id AND bc.organization_id = '. Organization::INTERNAL_OFFICE);
            $query2->andWhere(['bc.id' => null]);
        }

        $query1 = $this->setSessionCondition($query1, 'cr.billed_time');

        if ($this->src_physical_trunks_ids) {
            $query1->andWhere(['cr.trunk_id' => $this->src_physical_trunks_ids])
            && $query3
            && $query3
                ->leftJoin('auth.trunk t1', 'src_route = t1.trunk_name')
                ->andWhere(['t1.id' => $this->src_physical_trunks_ids]);
        }

        if ($this->dst_physical_trunks_ids) {
            $query2->andWhere(['cr.trunk_id' => $this->dst_physical_trunks_ids])
            && $query3
            && $query3
                ->leftJoin('auth.trunk t2', 'dst_route = t2.trunk_name')
                ->andWhere(['t2.id' => $this->dst_physical_trunks_ids]);
        }

        $query1 = $query1
            ->reportCondition('cr.trunk_service_id', $this->src_logical_trunks_ids)
            ->reportCondition('st.contract_id', $this->dst_contracts_ids)
            ->reportCondition('cr.nnp_operator_id', $this->dst_operator_ids)
            ->reportCondition('cr.nnp_region_id', $this->dst_regions_ids)
            ->reportCondition('cr.nnp_city_id', $this->dst_cities_ids)
            ->reportCondition('cr.nnp_country_code', $this->dst_countries_ids);

        $query2 = $query2
            ->reportCondition('cr.trunk_service_id', $this->dst_logical_trunks_ids)
            ->reportCondition('st.contract_id', $this->src_contracts_ids)
            ->reportCondition('cr.nnp_operator_id', $this->src_operator_ids)
            ->reportCondition('cr.nnp_region_id', $this->src_regions_ids)
            ->reportCondition('cr.nnp_city_id', $this->src_cities_ids)
            ->reportCondition('cr.nnp_country_code', $this->src_countries_ids);


        $isSrcNdcTypeGroup = in_array('src_ndc_type_id', $this->group);
        $isDstNdcTypeGroup = in_array('dst_ndc_type_id', $this->group);

        if ($isDstNdcTypeGroup || $this->dst_destinations_ids || $this->dst_number_type_ids) {
            $query1->leftJoin(
                ["dst_nr" => 'nnp.number_range'],
                "dst_nr.id = cr.nnp_number_range_id"
            );
        }

        if ($isSrcNdcTypeGroup || $this->src_destinations_ids || $this->src_number_type_ids) {
            $query1->leftJoin(
                ["src_nr" => 'nnp.number_range'],
                "src_nr.id = cr.nnp_number_range_id"
            );
        }

        $query1 = $this->setDestinationCondition($query1, $query3, $this->dst_destinations_ids, 'cr.nnp_number_range_id', $isDstNdcTypeGroup, 'dst');
        $query2 = $this->setDestinationCondition($query2, $query3, $this->src_destinations_ids, 'cr.nnp_number_range_id', $isSrcNdcTypeGroup, 'src');


        if ($this->src_number_type_ids) {
            $query2->andWhere(["src_nr.ndc_type_id" => $this->src_number_type_ids]);
        }

        if ($this->dst_number_type_ids) {
            $query1->andWhere(["dst_nr.ndc_type_id" => $this->dst_number_type_ids]);
        }

        if ($isDstNdcTypeGroup || $this->dst_number_type_ids) {
            $query1->addSelect(['dst_ndc_type_id' => 'dst_nr.ndc_type_id']);
        }

        if ($isSrcNdcTypeGroup || $this->src_number_type_ids) {
            $query2->addSelect(['src_ndc_type_id' => 'src_nr.ndc_type_id']);
        }

        if ($this->is_success_calls) {
            $condition = ['or', 'billed_time > 0', ['disconnect_cause' => DisconnectCause::$successCodes]];
            $query1->andWhere($condition);
            $query2->andWhere($condition);
            $query3 = null;
        }

        /** @var Query $query3 */

        if ($this->dst_number) {
            $this->dst_number = strtr($this->dst_number, ['.' => '_', '*' => '%']);
            $query1->andWhere(['LIKE', 'CAST(cr.dst_number AS varchar)', $this->dst_number, $isEscape = false]);
            $query2->andWhere(['LIKE', 'CAST(cr.dst_number AS varchar)', $this->dst_number, $isEscape = false]);
            $query3 && $query3->andWhere(['LIKE', 'CAST(cu.dst_number AS varchar)', $this->dst_number, $isEscape = false]);
        }

        if ($this->src_number) {
            $this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%']);
            $query1->andWhere(['LIKE', 'CAST(cr.src_number AS varchar)', $this->src_number, $isEscape = false]);
            $query2->andWhere(['LIKE', 'CAST(cr.src_number AS varchar)', $this->src_number, $isEscape = false]);
            $query3 && $query3->andWhere(['LIKE', 'CAST(cu.src_number AS varchar)', $this->src_number, $isEscape = false]);
        }

        if ($this->disconnect_causes) {
            $condition = ['cr.disconnect_cause' => $this->disconnect_causes];
            $query1->andWhere($condition)
            && $query2->andWhere($condition)
            && $query3
            && $query3->andWhere($condition);
        }

//        $query4->select(['*', '(@(cr1.sale)) - cr2.cost_price margin']
//        )->from('cr1')
//            ->join('JOIN', 'cr2', ['AND', 'cr1.cdr_id = cr2.cdr_id']);
//
//        $query4->addWith(['cr1' => $query1]);
//        $query4->addWith(['cr2' => $query2]);

        $query4
            ->select(['*', '(@(cr1.sale)) - cr2.cost_price margin'])
            ->from(['cr1' => $query1, 'cr2' => $query2])
            ->where('cr1.cdr_id = cr2.cdr_id');

        // временно отключим этот фунционал
        // $query3 && $query4 = (new CTEQuery())->from(['cr' => $query4->union($query3)]);

        if (($this->sort && $this->sort != 'connect_time') || $this->group || $this->group_period || $this->aggr) {
            $query1->orderBy([])->limit(-1);
            $query2->orderBy([])->limit(-1);
            $query3 && $query3->orderBy([])->limit(-1);
            $query4->orderBy([])->limit(-1);
        }

        return $query4;
    }
}