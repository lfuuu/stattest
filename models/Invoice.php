<?php

namespace app\models;

use app\classes\behaviors\InvoiceNextIdx;
use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property string $number
 * @property int $organization_id
 * @property string $bill_no
 * @property int $idx
 * @property int $type_id
 * @property string $date
 * @property float $sum
 *
 * @property-read Bill bill
 */
class Invoice extends ActiveRecord
{
    const TYPE_1 = 1;
    const TYPE_2 = 2;
    const TYPE_T = 3;

    public static $types = [self::TYPE_1, self::TYPE_2, self::TYPE_T];

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'invoice';
    }

    /**
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'InvoiceNextIdx' => InvoiceNextIdx::className(),
        ];
    }


    public function getBill()
    {
        return $this->hasOne(Bill::className(), ['bill_no' => 'bill_no']);
    }

    public static function getDate(Bill $bill, $typeId)
    {
        $date = new \DateTimeImmutable($bill->bill_date);

        switch ($typeId) {
            case self::TYPE_1:
                return $date->modify('first day of this month');
                break;
            case self::TYPE_2:
                return $date->modify('first day of previous month');
                break;
            default:
                return $date;
                break;
        }
    }
}
