<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class Trouble
 *
 * @property int $id
 * @property int $trouble_id
 * @property int $roistat_visit
 * @property float $roistat_price
 * @property-read Trouble $trouble
 */
class TroubleRoistat extends ActiveRecord
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['trouble_id', 'roistat_visit',], 'integer'],
            [['roistat_price',], 'double'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tt_troubles_roistat';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrouble()
    {
        return $this->hasOne(Trouble::class, ['id' => 'trouble_id']);
    }
}
