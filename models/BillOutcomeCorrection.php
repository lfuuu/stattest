<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class BillOutcomeCorrection
 *
 * @property integer $id
 * @property integer $correction_number
 * @property string $bill_no
 * @property string $original_bill_no
 */
class BillOutcomeCorrection extends ActiveRecord
{
     /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'correction_number'], 'integer'],
            [['bill_no', 'original_bill_no'], 'string'],
        ];
    }

     /**
     * @return string
     */
    public static function tableName()
    {
        return 'newbills_outcome_corrections';
    }

    public function GetDate() 
    {
        return $this->date_created;
    }

    public function attributeLabels()
    {
        return [
            'bill_no' => 'Номер счета',
            'original_bill_no' => 'Номер оригинального счета',
            'date_created' => 'Дата создания правки',
            'correction_number' => 'Номер корректировки (по счету)'
        ];
    }

}