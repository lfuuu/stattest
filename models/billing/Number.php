<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 */
class Number extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const TYPE_SRC = 1;
    const TYPE_DST = 2;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.number';
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
     * Вернуть список всех доступных значений
     *
     * @param int $typeId
     * @param int $serverId
     * @return string[]
     */
    public static function getList(
        $typeId = null,
        $serverId = null
    ) {
        return self::getListTrait(
            $isWithEmpty = false,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = [
                'AND',
                'show_in_stat',
                [
                    'AND',
                    $typeId ? ['type_id' => $typeId] : [],
                    $serverId ? ['server_id' => $serverId] : []
                ]
            ]
        );
    }
}