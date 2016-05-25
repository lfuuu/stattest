<?php
namespace app\classes\traits;

use Yii;
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
    // константы в trait нельзя, поэтому приходится использовать static-переменные
    public static $isNull = -1;
    public static $isNotNull = -2;

    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @param bool $isWithClosed
     * @return self[]
     */
    public static function getList($isWithEmpty = false, $isWithClosed = false)
    {
        $list = self::find()
            ->orderBy(self::getListOrderBy())
            ->indexBy('id')
            ->all();

        if ($isWithClosed) {
            $list = [
                    self::$isNull => '- ' . Yii::t('common', 'Is empty') . ' -',
                    self::$isNotNull => '- ' . Yii::t('common', 'Is not empty') . ' -',
                ] + $list;
        }

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
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