<?php

namespace app\classes\uu\model;

use LogicException;
use Yii;

/**
 * Периоды (день, месяц, квартал и т.д.)
 * Период может быть только 1 день или кратен месяцу. "Несколько дней" - нельзя, потому что будут технические сложности с определением границ периодов.
 *
 * @property integer $id
 * @property integer $dayscount
 * @property integer $monthscount
 * @property string $name
 */
class Period extends \yii\db\ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const ID_DAY = 1;
    const ID_MONTH = 2;
    const ID_QUARTER = 3;
    const ID_HALFYEAR = 4;
    const ID_YEAR = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_period';
    }

    /**
     */
    public function rules()
    {
        return [
            [['id', 'dayscount', 'monthscount'], 'integer'],
            [['name'], 'string'],
            [['name'], 'required'],
        ];
    }

    /**
     * Вернуть список всех доступных моделей
     * @return self[]
     */
    public static function getList()
    {
        return self::find()
            ->orderBy(['monthscount' => SORT_ASC])
            ->indexBy('id')
            ->all();
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Вернуть modify-параметр для DateTime
     * @return string
     */
    public function getModify()
    {
        if ($this->monthscount) {
            return sprintf('+%d months', $this->monthscount);
        } elseif ($this->dayscount) {
            return sprintf('+%d days', $this->dayscount);
        } else {
            throw new LogicException('Dayscount and monthscount are 0');
        }
    }
}
