<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use yii\db\Expression;

/**
 * Class EventCmdId
 *
 * @property string $id
 * @property string $created_dt
 */
class EventCmdId extends ActiveRecord
{
    const LIVE_TIME_SQL = '15 day';

    public static function tableName()
    {
        return 'event_cmd_id';
    }

    public static function add($id): bool
    {
        if (!$id) {
            return false;
        }

        $row = new self();
        $row->id = $id;
        if (!$row->save()) {
            throw new ModelValidationException($row);
        }

        return true;
    }

    public static function isExists($id): bool
    {
        if (!$id) {
            return true;
        }

        return self::find()->where(['id' => $id])->exists();
    }

    public static function clean()
    {
        self::deleteAll(new Expression('created_at < now() - interval ' . self::LIVE_TIME_SQL));
    }
}
