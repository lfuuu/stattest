<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property string $model
 * @property int $model_id
 * @property int $parent_model_id
 * @property int $user_id
 * @property string $created_at
 * @property string $action
 * @property string $data_json
 * @property string $prev_data_json
 *
 * @property-read User $user
 */
class HistoryChanges extends ActiveRecord
{
    const ACTION_INSERT = 'insert';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    const ACTION_TO_NAME = [
        self::ACTION_INSERT => 'Добавил',
        self::ACTION_UPDATE => 'Изменил',
        self::ACTION_DELETE => 'Удалил',
    ];

    const ACTION_TO_COLOR_CLASS = [
        self::ACTION_INSERT => 'success',
        self::ACTION_UPDATE => 'warning',
        self::ACTION_DELETE => 'danger',
    ];

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model' => 'Модель',
            'model_id' => 'ID модели',
            'parent_model_id' => 'ID модели родителя',
            'user_id' => 'Юзер',
            'created_at' => 'Дата',
            'action' => 'Действие',
            'prev_data_json' => 'Было',
            'data_json' => 'Стало',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'history_changes.history_changes';
    }

    public static function getDb()
    {
        return \Yii::$app->dbHistory;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return self::ACTION_TO_NAME[$this->action];
    }

    /**
     * @return string
     */
    public function getColorClass()
    {
        return self::ACTION_TO_COLOR_CLASS[$this->action];
    }
}