<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;

/**
 * Class BillExternal
 *
 * @property string $bill_no
 * @property string $ext_bill_no
 * @property string $ext_bill_no_date
 * @property string $ext_invoice_no
 * @property string $ext_invoice_date
 * @property string $ext_akt_no
 * @property string $ext_akt_date
 * @property-read Bill $bill
 */
class BillExternal extends ActiveRecord
{
    public $isHistoryVersioning = false;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'newbills_external';
    }

    /**
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }

    public static function saveValue($bill_no, $field, $value)
    {
        static $billExtStore = null;

        if (!isset($billExtStore[$bill_no])) {
            $billExt = self::findOne(['bill_no' => $bill_no]);

            if (!$billExt) {
                $billExt = new self();
                $billExt->bill_no = $bill_no;
            }

            $billExtStore[$bill_no] = $billExt;
        }

        /** @var self $billExt */
        $billExt = $billExtStore[$bill_no];

        if (!isset($billExt->attributeLabels()[$field])) {
            throw new \LogicException('Bad field: ' . $field);
        }

        if ($billExt->{$field} == $value) {
            return true;
        }

        $billExt->{$field} = $value;

        if (!$billExt->save()) {
            throw new ModelValidationException($billExt);
        }

        return true;
    }

    /**
     * Навзщение полей
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'bill_no' => 'Номер счета',
            'ext_bill_no' => 'Внешний счет',
            'ext_bill_date' => 'Дата внешнего счета',
            'ext_invoice_no' => 'Внешная с/ф',
            'ext_akt_no' => 'Внешний акт',
            'ext_akt_date' => 'Дата внешнего акта',
            'ext_invoice_date' => 'Дата внешней с/ф',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::class, ['bill_no' => 'bill_no']);
    }
}
