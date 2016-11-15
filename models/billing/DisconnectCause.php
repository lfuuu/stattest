<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

class DisconnectCause extends ActiveRecord
{

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.disconnect_cause';
    }

    /**
     * @return []
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @return self[]
     */
    public static function getList($isWithEmpty = false, $isWithNullAndNotNull = false)
    {
        $list = self::find()
            ->orderBy(self::getListOrderBy())
            ->indexBy('cause_id')
            ->all();

        return self::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

    /**
     * По какому полю сортировать для getList()
     * @return []
     */
    public static function getListOrderBy()
    {
        return ['cause_id' => SORT_ASC];
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

}