<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class TroubleFolder
 *
 * @property int $pk
 * @property string $name
 * @property string $order
 */
class TroubleFolder extends ActiveRecord
{
    const PK_TASK = 524033;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tt_folders';
    }
}