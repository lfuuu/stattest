<?php
namespace app\classes\behaviors;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use Yii;
use yii\base\Behavior;

class HistoryChanges extends Behavior
{
    /** @var ActiveRecord */
    public $owner;

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * After insert
     *
     * @throws \yii\db\Exception
     */
    public function afterInsert()
    {
        $this->_logChanges(\app\models\HistoryChanges::ACTION_INSERT, $this->owner->toArray(), null);
    }

    /**
     * Before update
     *
     * @throws \yii\db\Exception
     */
    public function beforeUpdate()
    {
        $this->_fillChanges($data, $prevData);
        if (!empty($data)) {
            $this->_logChanges(\app\models\HistoryChanges::ACTION_UPDATE, $data, $prevData);
        }
    }

    /**
     * After delete
     *
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        $this->_logChanges(\app\models\HistoryChanges::ACTION_DELETE, $this->owner->newHistoryData, $this->owner->toArray());
    }

    /**
     * @param string $action
     * @param mixed $data
     * @param mixed $prevData
     * @throws \yii\db\Exception
     */
    private function _logChanges($action, $data, $prevData)
    {
        $queryData = [
            'model' => $this->owner->getClassName(),
            'model_id' => $this->owner->primaryKey,
            'parent_model_id' => $this->owner->getParentId(),
            'user_id' => Yii::$app->user->getId(),
            'created_at' => date(DateTimeZoneHelper::DATETIME_FORMAT),
            'action' => $action,
            'data_json' => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'prev_data_json' => json_encode($prevData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];

        $params = [];
        $sql = \app\models\HistoryChanges::getDb()->queryBuilder
            ->insert(
                \app\models\HistoryChanges::tableName(),
                $queryData,
                $params
            );
        \app\models\HistoryChanges::getDb()->createCommand($sql, $params)->execute();
    }

    /**
     * @param array $result
     * @param array $resultOld
     */
    private function _fillChanges(&$result, &$resultOld)
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
