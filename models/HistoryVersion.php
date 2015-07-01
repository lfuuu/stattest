<?php

namespace app\models;

use yii\base\Model;
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
            
            $versions[$k]['diffs'] = $diffs;
        }
    }

    //Export the current version for the current object in the table
    public function exportCurrentVersion()
    {
        $modelClass = 'app\\models\\'.$this->model;
        $currentModel = $modelClass::findOne($this->model_id);

        $currentModel->setAttributes(json_decode($this->data_json, true), false);
        $currentModel->detachBehavior('HistoryVersion');
        return $currentModel->save();
    }

    public static function getVersionOnDate($modelName, $modelId, $date = null)
    {
        if(strpos($modelName, 'app\\models\\') === false)
            $modelClass = 'app\\models\\'.$modelName;
        else{
            $modelClass = $modelName;
            $modelName = substr($modelName, strlen('app\\models\\'));
        }

        $currentModel = $modelClass::findOne($modelId);

        if(null===$date && null!==$currentModel)
            return $currentModel;

        if(null === $date)
            $date = date('Y-m-d');

        if(null ===  $currentModel)
            $currentModel = new $modelClass();

        $historyModel = static::find()
            ->andWhere(['model' => $modelName])
            ->andWhere(['model_id' => $modelId])
            ->andWhere(['<=','date',$date])
            ->orderBy('date DESC')->one();

        $currentModel->setAttributes(json_decode($historyModel['data_json'], true), false);
        $currentModel->historyVersionDate = $date;

        return $currentModel;
    }
}
