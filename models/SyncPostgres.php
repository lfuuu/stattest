<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;

/**
 * Class SyncPostgres
 *
 * @property string $tbase enum ('nispd', 'auth', 'nispd_dev')
 * @property string $tname
 * @property integer $tid
 * @property string $rnd
 */
class SyncPostgres extends ActiveRecord
{
    const DEFAULT_TBASE = 'nispd';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'z_sync_postgres';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['tbase', 'default', 'value' => self::DEFAULT_TBASE],
            ['rnd', 'default', 'value' => rand(1000000, 10000000 - 1)],
            [['tname', 'tid'], 'required'],
            ['tname', 'string'],
            ['tid', 'integer'],
        ];
    }

    /**
     * @param string $table
     * @param int $id
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function registerSync(string $table, int $id): bool
    {
        $sync = new self();
        $sync->tname = $table;
        $sync->tid = $id;

        if (!$sync->validate()) {
            throw new ModelValidationException($sync);
        }

        $sql = self::getDb()->createCommand()->insert(self::tableName(), $sync->getDirtyAttributes())->rawSql . ' ON DUPLICATE KEY UPDATE rnd=rnd';

        return (bool)self::getDb()->createCommand($sql)->execute();
    }
}
