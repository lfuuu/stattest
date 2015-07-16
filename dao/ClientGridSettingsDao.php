<?php
namespace app\dao;

use app\classes\Singleton;
use yii\db\Query;

class ClientGridSettingsDao extends Singleton
{
    public $grids;
    private $attributeLabels = [];

    public function __construct()
    {
        $this->grids = \Yii::$app->params['clientGrid'];
    }

    public function getGridByBusinessProcessStatusId($id)
    {
        if($id > 0 && isset($this->grids['data'][$id])) {
            $gridSettings = $this->grids['data'][$id];
        } else{
            return [];
        }
        return  $this->constructGridSettingArray($gridSettings);
    }

    public function getGridByBusinessProcessId($id){
        $gridSettings= [];
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

        return $this->constructGridSettingArray($gridSettings);
    }

    public function getAllByParams($params, $genFilters = false)
    {
        $res = [];
        foreach ($this->grids['data'] as $v) {
            $addToRes = true;
            foreach($params as $paramKey => $paramValue){
                if($v[$paramKey] != $paramValue)
                    $addToRes = false;
            }
            if($addToRes){
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

    public function getTabList($bpId)
    {
        return $this->getAllByParams(['grid_business_process_id' => $bpId]);
    }

    public static function menuAsArray ()
    {
        $query = new Query();
        $rows = $query->select('ct.id,ct.name')
            ->from('client_contract_type ct')
            ->innerJoin('grid_business_process bp', 'bp.client_contract_id = ct.id')
            ->groupBy('ct.id')
            ->orderBy('ct.sort')
            ->all();

        foreach($rows as $row)
            $blocks_rows[$row['id']] = $row;

        foreach($blocks_rows as $key => $block_row)
        {
            $query = new Query();
            $query->addParams([':id' => $block_row['id']]);
            $blocks_items = $query->select('bp.id, bp.name, link')
                ->from('grid_business_process bp')
                ->orderBy('bp.sort')
                ->where('bp.client_contract_id = :id')
                ->all();

            foreach($blocks_items as $item)
            {
                if ( $item['link'] == null )
                {
                    $item['link'] = '/client/grid?bp='.$item['id'];
                }
                $blocks_rows[$key]['items'][] = $item;
            }
        }
        return $blocks_rows;
    }

    private function constructGridSettingArray($gridSettings, $genFilters = true)
    {
        $grids = $this->grids;
        if($gridSettings['queryParams'])
            $gridSettings['queryParams'] = array_merge_recursive($grids['defaultQueryParams'], $gridSettings['queryParams']);
        $columns = [];
        foreach($gridSettings['columns'] as $k => $v){
            $columnName = is_string($v) ? $v : $k;
            $columnParams = is_array($v) ? $v : [];
            $columns[$columnName] = isset($grids['defaultColumnsParams'][$columnName])
                ? array_merge_recursive($grids['defaultColumnsParams'][$columnName], $columnParams)
                : $columnParams ;

            $columns[$columnName]['label'] = isset($v['label'])
                ? $v['label']
                : (isset($grids['labels'][$columnName])? $grids['labels'][$columnName] : $this->attributeLabels[$columnName]);

            if($genFilters && $columns[$columnName]['filter'] instanceof \Closure) {
                $columns[$columnName]['filter'] = $columns[$columnName]['filter']();
            }
        }
        //var_dump($columns); die;
        $gridSettings['columns'] = $columns;
        return $gridSettings;
    }
}
