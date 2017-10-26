<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property string $name
 * @property int $server_id
 */
class TrunkGroup extends ActiveRecord
{
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'server_id' => 'Сервер',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk_group';
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
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Вернуть список значений
     *
     * @param null $serverIds
     * @param bool $isWithEmpty
     * @return mixed
     */
    public static function getList(
        $serverIds = null,
        $isWithEmpty = false
    ) {
        $serverIds = (int) $serverIds;
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $serverIds ? ['server_id' => $serverIds] : []
        );
    }

}