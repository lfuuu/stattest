<?php

namespace app\health;

use app\modules\nnp\models\PrefixDestination;

/**
 * ННП. Количество префиксов-направлений
 */
class MonitorNnpPrefix extends Monitor
{
    const MIN_VALUE = 500;

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 50, 100];
    }

    /**
     * Текущее значение
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function getValue()
    {
        // возвращает не кол-во, а насколько меньше MIN_VALUE. В идеале должно быть отрицательным (то есть большее MIN_VALUE)
        return self::MIN_VALUE - PrefixDestination::find()->count();
    }
}