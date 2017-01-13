<?php
namespace app\classes\behaviors;

use app\helpers\DateTimeZoneHelper;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class HistoryChanges
 *
 * @property ActiveRecord $owner
 */
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
     */
    public function afterInsert()
    {
        $this->_logChanges(\app\models\HistoryChanges::ACTION_INSERT, $this->owner->toArray(), null);
    }

    /**
     * Before update
     */
    public function beforeUpdate()
    {
        $this->fillChanges($data, $prevData);
        if (!empty($data)) {
            $this->_logChanges(\app\models\HistoryChanges::ACTION_UPDATE, $data, $prevData);
        }
    }

    /**
     * After delete
     */
    public function afterDelete()
    {
        $this->_logChanges(\app\models\HistoryChanges::ACTION_DELETE, null, $this->owner->toArray());
    }

    /**
     * @param string $action
     * @param string $data
     * @param mixed $prevData
     */
    private function _logChanges($action, $data, $prevData)
    {
        $queryData = [
            'model' => $this->_getClassName(),
            'model_id' => $this->owner->primaryKey,
            'user_id' => Yii::$app->user->getId(),
            'created_at' => date(DateTimeZoneHelper::DATETIME_FORMAT),
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

    /**
     * Подготавливает названия класса для работы с историей
     *
     * @return string
     */
    private function _getClassName()
    {
        return get_class($this->owner);
    }
}
