<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class GoodStore
 *
 * @property string good_id
 * @property string descr_id
 * @property integer qty_free
 * @property integer qty_store
 * @property integer qty_wait
 * @property string store_id
 */
class GoodStore extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'g_good_store';
    }
}
