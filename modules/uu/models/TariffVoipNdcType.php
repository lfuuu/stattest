<?php

namespace app\modules\uu\models;

use app\classes\model\HistoryActiveRecord;
use app\modules\nnp\models\NdcType;
use yii\db\ActiveQuery;

/**
 * Телефония. Типы NDC
 *
 * @property integer $id
 * @property integer $tariff_id
 * @property integer $ndc_type_id
 *
 * @property Tariff $tariff
 * @property NdcType $ndcType
 */
class TariffVoipNdcType extends HistoryActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @return array
     */
    public function behaviors()
    {
        return parent::behaviors() + [
                'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_voip_ndc_type';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'ndc_type_id',], 'integer'],
            [['tariff_id', 'ndc_type_id',], 'required'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNdcType()
    {
        return $this->hasOne(NdcType::className(), ['id' => 'ndc_type_id']);
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->ndcType->name;
    }

}
