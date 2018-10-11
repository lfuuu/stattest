<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property int $trunk_id
 * @property bool $orig
 * @property bool $outgoing
 * @property bool $allow
 * @property int $order
 * @property int $prefixlist_id
 * @property bool $test_redirect_num
 * @property int $num_n
 *
 * @property-read Trunk $trunk
 */
class TrunkAbfiltersRule extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk_abfilters_rule';
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

    /**
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getTrunk()
    {
        return $this
            ->hasOne(Trunk::class, ['id' => 'trunk_id'])
            ->one();
    }
}
