<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class Bik
 *
 * @property string $bik
 * @property string $corr_acc
 * @property string $bank_name
 * @property string $bank_city
 */
class Bik extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'bik';
    }

}
