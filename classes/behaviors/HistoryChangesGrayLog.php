<?php
namespace app\classes\behaviors;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use welltime\graylog\GelfMessage;
use welltime\graylog\GraylogTarget;
use Yii;
use yii\base\Behavior;
use yii\log\Logger;

class HistoryChangesGrayLog extends HistoryChanges
{
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

        return $this->_save($queryData);
    }

    protected function _save($queryData)
    {
        Yii::info($queryData);

        parent::_save($queryData);
/**/
        Yii::info(
            GelfMessage::create()
                ->setTimestamp(YII_BEGIN_TIME)
                ->setShortMessage('History for ' . $queryData['model'] . ' id: ' . $queryData['model_id'])
                ->setFullMessage(var_export($queryData, true))
                ->setAdditional('HistoryModel', $queryData['model'])
                ->setAdditional('HistoryModelId', $queryData['model_id'])
                ->setAdditional('UserLogin', Yii::$app->user && Yii::$app->user->identity ? Yii::$app->user->identity->user : ''),
            'history'
        );
/**/
    }
}
