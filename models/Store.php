<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Store
 *
 * @property string good_id
 * @property string descr_id
 * @property integer qty_free
 * @property integer qty_store
 * @property integer qty_wait
 * @property string store_id
 */
class Store extends ActiveRecord
{
    const MAIN_STORE = '8e5c7b22-8385-11df-9af5-001517456eb1';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'g_store';
    }
}
