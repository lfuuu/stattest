<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use yii\db\Expression;

/**
 * @property integer $id
 * @property string $bill_no
 * @property integer $type_id
 * @property integer $number
 * @property string $date
 * @property-read Bill bill
 * @property-read BillLineCorrection[] lines
 */
class BillCorrection extends ActiveRecord
{
    public $isHistoryVersioning = false;

    const TYPE_INVOICE_1 = 1;
    const TYPE_INVOICE_2 = 2;

    public static $typeList = [
        self::TYPE_INVOICE_1 => 'с/ф 1',
        self::TYPE_INVOICE_2 => 'с/ф 2',
    ];

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'newbills_correction';
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

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['date', 'safe']
        ];
    }


    /**
     * @return array
     */
    public function transactions()
    {
        return [
            'default' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
        ];
    }

    /**
     * Название полей
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'bill_no' => 'Номер счета',
            'type_id' => 'Тип документа',
            'number' => 'Номер документа',
            'date' => 'Дата документа'
        ];
    }

    public function afterSave($isInsert, $changedAttributes)
    {
        if (!$isInsert) {
            return;
        }

        foreach ($this->_getOriginalBillLinesByTypeId($this->type_id) as $line) {
            $newLine = new BillLineCorrection();

            if ($line instanceof BillLine) {
                $data = $line->getAttributes(null, ['pk']);
            } else {
                // array
                $data = $line;
                unset($data['pk']);
            }

            $newLine->setAttributes($data, false);
            $newLine->bill_correction_id = $this->id;
            if (!$newLine->save()) {
                throw new ModelValidationException($newLine);
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::class, ['bill_no' => 'bill_no']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLines()
    {
        return $this->hasMany(BillLineCorrection::class, ['bill_correction_id' => 'id']);
    }

    /**
     * Информация о корректирующих документа
     *
     * @param Bill $bill
     * @return array
     */
    public static function getInfo(Bill $bill)
    {
        $info = self::find()
            ->select(new Expression('MAX(number)'))
            ->where(['bill_no' => $bill->bill_no])
            ->groupBy('type_id')
            ->indexBy('type_id')
            ->column();

        return $info;
    }

    /**
     * Получение корректирующих позиций
     *
     * @param string $billNo
     * @param integer $typeId
     * @return array|null
     */
    public static function getBillLines($billNo, $typeId)
    {
        $billCorrection = self::findOne(['bill_no' => $billNo, 'type_id' => $typeId]);

        if (!$billCorrection) {
            return null;
        }

        return $billCorrection->getLines()->asArray()->all();
    }

    /**
     * Пересчитать скорректированную сумму счета
     * @throws ModelValidationException
     */
    public function recalcSumCorrection()
    {
        $lines = [];

        $bill = $this->bill;
        $billSum = $bill->sum;
        $billInfo = $bill->getCorrectionInfo();

        $isBillCorrected = false;
        foreach (self::$typeList as $typeId => $typeName) {
            if (isset($billInfo[$typeId])) {
                // берем позиции из корректировки
                $newLines = BillLineCorrection::find()
                    ->joinWith('billCorrection bc')
                    ->where(['bc.bill_no' => $bill->bill_no, 'bc.type_id' => $typeId])
                    ->all();
                $isBillCorrected = true;
            } else {
                $newLines = $this->_getOriginalBillLinesByTypeId($typeId);
            }

            $lines = array_merge($lines, $newLines);
        }

        // нет корректирующих позиций
        if (!$isBillCorrected) {
            if ($bill->sum_correction != null) {
                $bill->sum_correction = null;
                if (!$bill->save()) {
                    throw new ModelValidationException($bill);
                }
            }
            return;
        }

        $linesSum = $this->_getSumInLines($lines);

        if (abs($billSum - $linesSum) >= 0.01) {
            $bill->sum_correction = $linesSum;
            if (!$bill->save()) {
                throw new ModelValidationException($bill);
            }
        }
    }

    /**
     * Получаем сумму в позициях документа
     *
     * @param array $lines
     * @return float
     */
    private function _getSumInLines($lines)
    {
        return array_reduce($lines,
            function ($sum, $line) {
                $sum += $line->sum;
                return $sum;
            }, 0);
    }

    /**
     * Получение позиций счета по типу документа
     *
     * @param integer $typeId
     * @return array
     */
    private function _getOriginalBillLinesByTypeId($typeId)
    {
        return $this->bill->getLinesByTypeId($typeId);
    }

    /**
     * Дата счета фактуры в timestamp
     *
     * @return int
     */
    public function getInvoiceDate()
    {
        return (new \DateTime($this->bill->bill_date))
            ->modify('last day of ' . ($this->type_id == self::TYPE_INVOICE_1 ? 'this' : 'previous') . ' month')
            ->getTimestamp();
    }

    /**
     * Название клиента
     *
     * @return string
     */
    public function getClientAccount()
    {
        return $this->bill->clientAccount;
    }

    /**
     * Сумма позиций изначального документа
     *
     * @return float
     */
    public function getOriginalSum()
    {
        $lines = $this->_getOriginalBillLinesByTypeId($this->type_id);

        return $this->_getSumInLines($lines);
    }

    /**
     * Сумма скорректированной с/ф
     * 
     * @return float
     */
    public function getSum()
    {
        return $this->_getSumInLines($this->lines);
    }

}
