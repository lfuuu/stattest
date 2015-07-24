<?php
namespace app\classes\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class HistoryChanges extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    public function afterInsert()
    {
        $this->logChanges(\app\models\HistoryChanges::ACTION_INSERT, $this->owner->toArray(), null);
    }

    public function beforeUpdate()
    {
        $this->fillChanges($data, $prevData);
        if (!empty($data)) {
            $this->logChanges(\app\models\HistoryChanges::ACTION_UPDATE, $data, $prevData);
        }
    }

    public function afterDelete()
    {
        $this->logChanges(\app\models\HistoryChanges::ACTION_DELETE, null, $this->owner->toArray());
    }

    private function logChanges($action, $data, $prevData)
    {
        $queryData =[
            'model' => substr(get_class($this->owner), 11), // remove 'app\models\'
            'model_id' => $this->owner->primaryKey,
            'user_id' => Yii::$app->user->getId(),
            'created_at' => date('Y-m-d H:i:s'),
            'action' => $action,
            'data_json' => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'prev_data_json' => json_encode($prevData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];

        $sql = Yii::$app->db->queryBuilder
                ->insert(
                    \app\models\HistoryChanges::tableName(),
                    $queryData,
                    $params
                );
        Yii::$app->db->createCommand($sql, $params)->execute();

    }

    private function fillChanges(&$result, &$resultOld)
    {
        $attributes = $this->owner->getAttributes();
        $oldAttributes = $this->owner->getOldAttributes();

        $result = [];
        $resultOld = [];

        foreach ($attributes as $name => $value) {
            if (array_key_exists($name, $oldAttributes)) {
                if ($value != $oldAttributes[$name]) {
                    $result[$name] = (string)$value;
                    $resultOld[$name] = (string)$oldAttributes[$name];
                }
            } else {
                $result[$name] = (string)$value;
                $resultOld[$name] = '';
            }
        }
    }
}
