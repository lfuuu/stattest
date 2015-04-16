<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int        $id
 * @property string     $model
 * @property int        $model_id
 * @property int        $user_id
 * @property string     $created_at
 * @property string     $action
 * @property string     $data_json
 * @property string     $prev_data_json

 * @property User       $user
 * @property
 */
class HistoryChanges extends ActiveRecord
{
    const ACTION_INSERT = 'insert';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    public static function tableName()
    {
        return 'history_changes';
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}