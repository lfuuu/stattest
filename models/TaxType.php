<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $id             идентификатор налога (0, 10, 18, 10110, 18118)
 * @property string $name           текстовое представление для отчетов
 * @property string $rate           коэффициект на который нужно умножить чтобы получить сумму налога
 * @property
 */
class TaxType extends ActiveRecord
{
    public static function tableName()
    {
        return 'tax_type';
    }
}