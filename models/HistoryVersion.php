<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property string     $model
 * @property int        $model_id
 * @property string     $date
 * @property string     $data_json
 */
class HistoryVersion extends ActiveRecord
{
    public static function tableName()
    {
        return 'history_version';
    }

    public static function generateVersionsJson(array $versions)
    {
        $arr = [];
        foreach ($versions as $version)
            $arr[] = '["' . $version['model'] . '","' . $version['model_id'] . '","' . $version['date'] . '",' . $version['data_json'] . ']';

        return '[' . implode(',', $arr) . ']';
    }
    
    public static function generateDifferencesFor(&$versions)
    {
        for($k=0, $count = count($versions) ; $k<$count;$k++){
            $versions[$k]['data_json'] = json_decode($versions[$k]['data_json'], true);
            
            $diffs = [];
            if($k>0)
            {
                $oldKeys = array_diff_key($versions[$k-1]['data_json'], $versions[$k]['data_json']);
                foreach ($oldKeys as $key)
                    $diffs[$key] = [$versions[$k-1]['data_json'][$key], ''];
                
                foreach ($versions[$k]['data_json'] as $key=>$val)
                    if(!isset($versions[$k-1]['data_json'][$key]))
                        $diffs[$key] = ['', $val];
                    elseif($versions[$k-1]['data_json'][$key] != $val)
                        $diffs[$key] = [$versions[$k-1]['data_json'][$key], $val];
            }
            
            if($k==2){
                var_dump($diffs);die;
            }
            
            $versions[$k]['diffs'] = $diffs;
        }
    }

}
