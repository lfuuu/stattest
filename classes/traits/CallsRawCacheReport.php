<?php
/**
 * Формирование отчета путем обращения к кеширующей базе данных
 */

namespace app\classes\traits;

use app\classes\yii\CTEQuery;
use Yii;
use yii\db\Expression;
use app\models\billing\DisconnectCause;

trait CallsRawCacheReport
{
    /**
     * @return CTEQuery
     */
    private function getCacheReport()
    {
        $this->dbConn = Yii::$app->dbPgSlaveCache;

        $query = new CTEQuery();

        $query->select(
            [
                'connect_time',
                'disconnect_cause',
                'src_route',
                'src_number',
                'dst_number',
                'pdd',
                'src_operator_name',
                'src_country_name',
                'src_region_name',
                'src_city_name',
                'src_contract_name',
                'sale',
                'orig_rate',
                'session_time',
                'dst_route',
                'dst_operator_name',
                'dst_country_name',
                'dst_region_name',
                'dst_city_name',
                'dst_contract_name',
                'cost_price',
                'term_rate',
                'margin',
            ]
        )
            ->from('calls_raw_cache.calls_raw_cache');

        if ($this->connect_time_from || $this->correct_connect_time_to) {
            $query->andWhere(
                [
                    'BETWEEN',
                    'connect_time',
                    $this->connect_time_from,
                    $this->correct_connect_time_to ? $this->correct_connect_time_to : new Expression('now()'),
                ]
            );
        }

        $query = $this->setSessionCondition($query, 'session_time');

        $query = $query
            ->reportCondition('server_id', $this->server_ids)
            ->reportCondition('src_trunk_id', $this->src_physical_trunks_ids)
            ->reportCondition('dst_trunk_id', $this->dst_physical_trunks_ids)
            ->reportCondition('src_trunk_service_id', $this->src_logical_trunks_ids)
            ->reportCondition('dst_trunk_service_id', $this->dst_logical_trunks_ids)
            ->reportCondition('src_contract_id', $this->src_contracts_ids)
            ->reportCondition('dst_contract_id', $this->dst_contracts_ids)
            ->reportCondition('src_nnp_operator_id', $this->src_operator_ids)
            ->reportCondition('src_nnp_region_id', $this->src_regions_ids)
            ->reportCondition('src_nnp_city_id', $this->src_cities_ids)
            ->reportCondition('src_nnp_country_code', $this->src_countries_ids)
            ->reportCondition('dst_nnp_operator_id', $this->dst_operator_ids)
            ->reportCondition('dst_nnp_region_id', $this->dst_regions_ids)
            ->reportCondition('dst_nnp_city_id', $this->dst_cities_ids)
            ->reportCondition('dst_nnp_country_code', $this->dst_countries_ids)
            ->reportCondition('disconnect_cause', $this->disconnect_causes);

        $isSrcNdcTypeGroup = in_array('src_ndc_type_id', $this->group) ? true : false;
        $isDstNdcTypeGroup = in_array('dst_ndc_type_id', $this->group) ? true : false;

        $query = $this->setDestinationCondition($query, $this->src_destinations_ids, $this->src_number_type_ids, 'src_nnp_number_range_id', $isSrcNdcTypeGroup, 'src');
        $query = $this->setDestinationCondition($query, $this->dst_destinations_ids, $this->dst_number_type_ids, 'dst_nnp_number_range_id', $isDstNdcTypeGroup, 'dst');

        $this->is_success_calls
        && $query->andWhere(['or', 'session_time > 0', ['disconnect_cause' => DisconnectCause::$successCodes]]);

        if ($this->dst_number) {
            $this->dst_number = strtr($this->dst_number, ['.' => '_', '*' => '%']);
            $query->andWhere(['LIKE', 'dst_number', $this->dst_number, $isEscape = false]);
        }

        if ($this->src_number) {
            $this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%']);
            $query->andWhere(['LIKE', 'src_number', $this->src_number, $isEscape = false]);
        }

        return $query;
    }
}