<?php
namespace app\dao;

use app\classes\Singleton;

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

    public function getAllByParams($params)
    {
        $res = [];
        foreach ($this->grids['data'] as $k => $v) {
            $addToRes = true;
            foreach($params as $paramKey => $paramValue){
                if($v[$paramKey] != $paramValue)
                    $addToRes = false;
            }
            if($addToRes){
                $res[$v['id']] = $this->constructGridSettingArray($v);
            }
        }
        return $res;
    }

    public function setAttributeLabels($labels)
    {
        $this->attributeLabels = $labels;
        return $this;
    }

    private function constructGridSettingArray($gridSettings)
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

            if($columns[$columnName]['filter'] instanceof \Closure) {
                $columns[$columnName]['filter'] = $columns[$columnName]['filter']();
            }
        }
        //var_dump($columns); die;
        $gridSettings['columns'] = $columns;
        return $gridSettings;
    }
}
