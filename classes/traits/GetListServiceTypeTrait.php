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
trait GetListServiceTypeTrait
{
    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @return self[]
     */
    public static function getList($serviceTypeId = null, $isWithEmpty = false)
    {
        $list = self::find()
            ->where('service_type_id IS NULL')
            ->orWhere('service_type_id = :service_type_id', [':service_type_id' => $serviceTypeId])
            ->orderBy(self::getListOrderBy())
            ->indexBy('id')
            ->all();

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

    /**
     * По какому полю сортировать для getList()
     * @return array
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