<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class IpBlock
 *
 * @property string $ip
 * @property string $block_time
 * @property string $unblock_time
 *
 * @package app\models
 */
class IpBlock extends ActiveRecord
{
    public static function tableName()
    {
        return 'ip_block';
    }
}
