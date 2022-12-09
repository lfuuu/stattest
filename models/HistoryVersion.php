<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\classes\model\HistoryActiveRecord;

/**
 * @property string $model
 * @property int $model_id
 * @property string $date
 * @property string $data_json
 * @property int $user_id
 *
 * @property-read User $user
 */
class HistoryVersion extends ActiveRecord
{

    public $diffs = [];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'history_version';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param $versions
     */
    public static function generateDifferencesFor(&$versions)
    {
        for ($k = 0, $count = count($versions); $k < $count; $k++) {
            $versions[$k]['data_json'] = json_decode($versions[$k]['data_json'], true);

            $diffs = [];
            if ($k > 0) {
                $oldKeys = array_diff_key($versions[$k - 1]['data_json'], $versions[$k]['data_json']);
                foreach ($oldKeys as $key => $value) {
                    $diffs[$key] = [$versions[$k - 1]['data_json'][$key], ''];
                }

                foreach ($versions[$k]['data_json'] as $key => $val) {
                    $oldVal = isset($versions[$k - 1]['data_json'][$key]) ? $versions[$k - 1]['data_json'][$key] : '';

                    if ($oldVal != $val) {
                        $diffs[$key] = [$oldVal, $val];
                    }
                }
            }

            $versions[$k]['diffs'] = $diffs;
        }
    }

    /**
     * Export the current version for the current object in the table
     * @return mixed
     */
    public function exportCurrentVersion()
    {
        /** @var HistoryActiveRecord $currentModel */
        $className = $this->model;
        $currentModel = $className::findOne($this->model_id);
        $currentModel->fillHistoryDataInModel(json_decode($this->data_json, $assoc = true));
        $currentModel->isHistoryVersioning = false; // что бы не перезаписывать уже сохраненную версию

        return $currentModel->save(false);
    }
}
