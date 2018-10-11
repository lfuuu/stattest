<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property string $manual_list
 * @property int $type_id
 * @property bool $rossvyaz_mob
 * @property string $rossvyaz_country
 * @property string $rossvyaz_region
 * @property string $rossvyaz_city
 * @property int $rossvyaz_country_id
 * @property int $rossvyaz_region_id
 * @property int $rossvyaz_city_id
 * @property string $rossvyaz_operators
 * @property int $rossvyaz_operator_ids
 * @property int $count
 * @property bool $exclude_operators
 * @property int $smezhnost_list
 * @property int $network_config_id
 * @property bool $sw_shared
 * @property string $nnp_filter_json
 * @property bool $is_auto_update
 * @property string $dt_update
 * @property string $dt_prepare
 * @property bool $is_global
 * @property int $version
 * @property bool $invert
 *
 */
class Prefixlist extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.prefixlist';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }
}
