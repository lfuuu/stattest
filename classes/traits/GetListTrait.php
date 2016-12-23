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
     * Вернуть список всех доступных моделей
     *
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @return array
     */
    public static function getList($isWithEmpty = false, $isWithNullAndNotNull = false)
    {
        $list = self::find()
            ->orderBy(self::getListOrderBy())
            ->indexBy('id')
            ->all();

        return self::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

    /**
     * Вернуть пустой список без конкретных моделей
     *
     * @param bool $isWithEmpty
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
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

    /**
     * По какому полю сортировать для getList()
     *
     * @return array
     */
    public static function getListOrderBy()
    {
        return ['name' => SORT_ASC];
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