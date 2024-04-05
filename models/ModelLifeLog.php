<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Регистрация основных действий с моделью
 *
 * @property int $id
 * @property int $model
 * @property int $model_id
 * @property int $created_at
 * @property int $action
 */
class ModelLifeLog extends ActiveRecord
{
    const LIFETIME = 86400 * 3; // 3 day

    const DO_INSERT = 'insert';
    const DO_UPDATE = 'update';
    const DO_DELETE = 'delete';

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ]
        ];
    }

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'z_model_life_log';
    }

    public static function log($model_name, $model_id, $action = self::DO_INSERT)
    {
        if (rand(1, 20) == 1) {
            self::clean();
        }

        $log = new self();
        $log->model = $model_name;
        $log->model_id = $model_id;
        $log->action = $action;

        if (!$log->save()) {
            throw new ModelValidationException($log);
        }

        return $log;
    }

    public static function clean()
    {
        return self::DeleteAll(['<', 'created_at', new Expression('DATE_ADD(NOW(), INTERVAL -' . self::LIFETIME . ' second)')]);
    }

}
