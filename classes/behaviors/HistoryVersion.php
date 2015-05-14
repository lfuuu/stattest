<?php

namespace app\classes\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class HistoryVersion extends Behavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'addVersion',
            ActiveRecord::EVENT_AFTER_UPDATE => 'addVersion'
        ];
    }

    public function addVersion()
    {
        $queryData = [
            'model' => substr(get_class($this->owner), 11),
            'model_id' => $this->owner->primaryKey,
            'date' => date('Y-m-d'),
            'data_json' => json_encode($this->owner->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];

        $model = \app\models\HistoryVersion::findOne([
                    'model' => $queryData['model'],
                    'model_id' => $queryData['model_id'],
                    'date' => $queryData['date'],
        ]);
        if ($this->chechDiff($queryData) === false)
            return;

        if ($model === null)
            $model = new \app\models\HistoryVersion($queryData);
        
        $model->data_json = json_encode($this->owner->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
        $model->save();
    }

    private function chechDiff($queryData)
    {
        $model = \app\models\HistoryVersion::find([
                    'model' => $queryData['model'],
                    'model_id' => $queryData['model_id'],
                ])
                ->andWhere('date < :date', ['date'=> $queryData['date']])
                ->orderBy(['date' => SORT_DESC])
                ->one();
        
        
        if($model===null || $model->data_json != $queryData['data_json'])
            return true;
        return false;
    }

}
