<?php
namespace app\classes\traits;

use Yii;
use yii\db\ActiveQuery;

/**
 * Определяет getList (список для selectbox) и __toString
 *
 * @method static ActiveQuery find()
 */
trait GetListTrait
{
    // константы в trait нельзя, поэтому приходится использовать static-переменные
    public static $isNull = -1;
    public static $isNotNull = -2;

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param string $indexBy поле-ключ
     * @param string $select поле-значение
     * @param array $orderBy
     * @param array $where
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $indexBy = 'id',
        $select = 'name',
        $orderBy = ['name' => SORT_ASC],
        $where = []
    ) {
        $list = self::find()
            ->select([$select, $indexBy])
            ->where($where)
            ->orderBy($orderBy)
            ->indexBy($indexBy)
            ->column();

        return self::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

    /**
     * Вернуть список с универсальными фильтрами без конкретных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getEmptyList($isWithEmpty = false, $isWithNullAndNotNull = false)
    {
        $list = [];

        if ($isWithNullAndNotNull) {
            $list = [
                    GetListTrait::$isNull => '- ' . Yii::t('common', 'Is empty') . ' -',
                    GetListTrait::$isNotNull => '- ' . Yii::t('common', 'Is not empty') . ' -',
                ] + $list;
        }

        if ($isWithEmpty) {
            $list = ['' => is_string($isWithEmpty) ? $isWithEmpty : '----'] + $list;
        }

        return $list;
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
}