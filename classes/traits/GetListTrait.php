<?php
namespace app\classes\traits;

use yii\db\ActiveQuery;

/**
 * Определяет getList (список для selectbox) и __toString
 *
 * @method static ActiveQuery find()
 * @property int $id
 * @property string $name
 */
trait GetListTrait
{
    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @return self[]
     */
    public static function getList($isWithEmpty = false)
    {
        $list = self::find()
            ->orderBy(self::getListOrderBy())
            ->indexBy('id')
            ->all();

        if ($isWithEmpty) {
            $list = array_merge(['' => ' ---- '], $list);
        }

        return $list;
    }

    /**
     * По какому полю сортировать для getList()
     * @return []
     */
    public static function getListOrderBy()
    {
        return ['name' => SORT_ASC];
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}