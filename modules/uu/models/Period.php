<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use DateTimeImmutable;
use LogicException;

/**
 * Периоды (день, месяц, квартал и т.д.)
 * Период может быть только 1 день или кратен месяцу. "Несколько дней" - нельзя, потому что будут технические сложности с определением границ периодов.
 *
 * @property integer $id
 * @property integer $dayscount
 * @property integer $monthscount
 * @property string $name
 *
 * @method static Period findOne($condition)
 * @method static Period[] findAll($condition)
 */
class Period extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const ID_DAY = 1;
    const ID_MONTH = 2;
    const ID_QUARTER = 3;
    const ID_HALFYEAR = 4;
    const ID_YEAR = 5;

    const OPEN_DATE = '3000-01-01';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_period';
    }

    /**
     * @return array
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
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['monthscount' => SORT_ASC],
            $where = []
        );
    }

    /**
     * Вернуть modify-параметр для DateTime
     *
     * @param bool $isPositive
     * @return string
     */
    public function getModify($isPositive = true)
    {
        if ($this->monthscount) {
            return sprintf(($isPositive ? '+' : '-') . '%d months', $this->monthscount);
        } elseif ($this->dayscount) {
            return sprintf(($isPositive ? '+' : '-') . '%d days', $this->dayscount);
        } else {
            throw new LogicException('Dayscount and monthscount are 0');
        }
    }

    /**
     * Найти конец диапазона, начавшегося $dateTimeFrom, чтобы он содержал в себе $dateTimeMin
     * Он должен являться концом месяца/квартала/полугода/года
     *
     * @param DateTimeImmutable $dateTimeFrom
     * @param DateTimeImmutable $dateTimeMin
     * @return DateTimeImmutable
     */
    public function getMaxDateTo(DateTimeImmutable $dateTimeFrom, DateTimeImmutable $dateTimeMin)
    {
        if (!$this->monthscount) {
            // посуточная оплата - до начала нового периода
            return $dateTimeMin;
        }

        /** @var DateTimeImmutable $dateTimeFromTmp */
        $dateTimeFromTmp = clone $dateTimeFrom;

        while (true) {
            // конец текущего периода
            $dateTimeFromTmp = $this->getMinDateTo($dateTimeFromTmp);

            if ($dateTimeFromTmp->format(DateTimeZoneHelper::DATE_FORMAT) >= $dateTimeMin->format(DateTimeZoneHelper::DATE_FORMAT)) {
                return $dateTimeFromTmp;
            }

            // начать следующий период
            $dateTimeFromTmp = $dateTimeFromTmp->modify('+1 day');
        }
    }

    /**
     * Вернуть конец текущего периода
     * Посуточно: этот же день
     * Помесячно и более: первый неполный месяц + оставшиеся полные месяцы (последний день)
     *
     * @param DateTimeImmutable $dateTimeFrom
     * @return DateTimeImmutable
     */
    public function getMinDateTo(DateTimeImmutable $dateTimeFrom)
    {
        if (!$this->monthscount) {
            // Посуточно: этот же день
            return $dateTimeFrom;
        }

        // Помесячно и более: первый неполный месяц + оставшиеся полные месяцы (последний день)
        // просто прибавить месяцы нельзя, потому что неоднозначно, чему будет равно "31 января + 1 месяц". Поэтому временно переводим на 1 число следующего месяца, а потом на последний предыдущего
        return $dateTimeFrom
            ->modify('last day of this month')
            ->modify('+1 day')
            ->modify('+' . ($this->monthscount - 1) . ' months')
            ->modify('-1 day');
    }
}
