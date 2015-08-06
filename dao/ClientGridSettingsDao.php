<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\ClientBPStatuses;
use yii\db\ActiveQuery;
use yii\db\Query;

class ClientGridSettingsDao extends Singleton
{
    public $grids;
    private $attributeLabels = [];

    public function __construct()
    {
        $this->grids = \Yii::$app->params['clientGrid'];
    }

    public function getGridByBusinessProcessStatusId($id, $genFilters = true)
    {
        if ($id > 0 && isset($this->grids['data'][$id])) {
            $gridSettings = $this->grids['data'][$id];
        } else {
            return [];
        }
        return $this->constructGridSettingArray($gridSettings, $genFilters);
    }

    public function getGridByBusinessProcessId($id, $genFilters = true)
    {
        $gridSettings = [];
        $first = true;
        foreach ($this->grids['data'] as $k => $v) {
            if ($v['grid_business_process_id'] == $id) {
                if ($first || $v['default']) {
                    $gridSettings = $v;
                    $first = false;
                }
                if ($v['default'])
                    break;
            }
        }

        return $this->constructGridSettingArray($gridSettings, $genFilters);
    }

    public function getAllByParams($params = [], $genFilters = false)
    {
        $res = [];
        foreach ($this->grids['data'] as $v) {
            $addToRes = true;
            if ($params) {
                foreach ($params as $paramKey => $paramValue) {
                    if ($v[$paramKey] != $paramValue)
                        $addToRes = false;
                }
            }
            if ($addToRes) {
                $res[$v['id']] = $this->constructGridSettingArray($v, $genFilters);
            }
        }
        return $res;
    }

    public function setAttributeLabels($labels)
    {
        $this->attributeLabels = $labels;
        return $this;
    }

    public function getTabList($bpId, $getCount = true)
    {
        $rows = $this->getAllByParams(['grid_business_process_id' => $bpId]);
        if($getCount){
            foreach($rows as &$row){
                if(!(isset($row['hideCount']) && $row['hideCount'])) {
                    $query = new Query();
                    $params = $row['queryParams'];
                    unset($params['orderBy']);
                    foreach ($params as $paramKey => $param) {
                        $query->$paramKey = $param;
                    }
                    if ($row['id'] == ClientBPStatuses::FOLDER_TELECOM_AUTOBLOCK) {
                        $pg_query = new Query();
                        $pg_query->select('client_id')->from('billing.locks')->where('voip_auto_disabled=true');

                        $ids = $pg_query->column(\Yii::$app->dbPg);
                        if (!empty($ids)) {
                            $query->andFilterWhere(['in', 'c.id', $ids]);
                        }
                    }
                    $row['count'] = $query->count();
                }
            }
        }
        return $rows;
    }

    public static function menuAsArray()
    {
        $query = new Query();
        $rows = $query->select('ct.id,ct.name')
            ->from('client_contract_type ct')
            ->innerJoin('grid_business_process bp', 'bp.client_contract_id = ct.id')
            ->groupBy('ct.id')
            ->orderBy('ct.sort')
            ->all();

        foreach ($rows as $row) {
            $blocks_rows[$row['id']] = $row;
        }

        foreach ($blocks_rows as $key => $block_row) {
            $query = new Query();
            $query->addParams([':id' => $block_row['id']]);
            $blocks_items = $query->select('bp.id, bp.name, link')
                ->from('grid_business_process bp')
                ->orderBy('bp.sort')
                ->where('bp.client_contract_id = :id')
                ->all();

            foreach ($blocks_items as $item) {
                if ($item['link'] == null) {
                    $item['link'] = '/client/grid?bp=' . $item['id'];
                }
                $blocks_rows[$key]['items'][] = $item;
            }
        }

        foreach ($rows as $row) {
            $blocks_rows[$row['id'] . '_2'] = $row;
        }

        foreach ($blocks_rows as $key => $block_row) {
            $query = new Query();
            $query->addParams([':id' => $block_row['id']]);
            $blocks_items = $query->select('bp.id, bp.name, link')
                ->from('grid_business_process bp')
                ->orderBy('bp.sort')
                ->where('bp.client_contract_id = :id')
                ->all();

            foreach ($blocks_items as $item) {
                if ($item['link'] == null) {
                    $item['link'] = '/client/grid2?businessProcessId=' . $item['id'];
                }
                $blocks_rows[$key]['items'][] = $item;
            }
        }
        return $blocks_rows;
    }

    private function constructGridSettingArray($gridSettings, $genFilters = true)
    {
        if (isset($gridSettings['queryParams']) && is_array($gridSettings['queryParams']))
            $gridSettings['queryParams'] = array_merge_recursive($this->grids['defaultQueryParams'], $gridSettings['queryParams']);
        $columns = [];
        foreach ($gridSettings['columns'] as $k => $column) {
            $columnName = is_string($column) ? $column : $k;
            $columnParams = is_array($column) ? $column : [];
            $columns[$columnName] =
                isset($this->grids['defaultColumnsParams'][$columnName])
                    ? array_merge_recursive($this->grids['defaultColumnsParams'][$columnName], $columnParams)
                    : $columnParams;

            $columns[$columnName]['label'] = $this->spawnColumnLabel($column, $columnName);

            if ($genFilters && $columns[$columnName]['filter']) {
                $callback =
                    !is_array($columns[$columnName]['filter'])
                        ? $columns[$columnName]['filter']
                        : array_pop($columns[$columnName]['filter']);

                if ($callback  instanceof \Closure)
                    $columns[$columnName]['filter'] = $callback();
            }
        }
        //var_dump($columns); die;
        $gridSettings['columns'] = $columns;
        return $gridSettings;
    }

    private function spawnColumnLabel($column, $columnName)
    {
        if (isset($column['label'])) {
            return $column['label'];
        }
        if (isset($this->grids['labels'][$columnName])) {
            return $this->grids['labels'][$columnName];
        }
        if (isset($this->attributeLabels[$columnName])) {
            return $this->attributeLabels[$columnName];
        }
        return $columnName;
    }
}
