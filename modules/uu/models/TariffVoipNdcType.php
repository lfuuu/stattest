<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\modules\nnp\models\NdcType;
use yii\db\ActiveQuery;

/**
 * Телефония. Типы NDC
 *
 * @property integer $id
 * @property integer $tariff_id
 * @property integer $ndc_type_id
 *
 * @property-read Tariff $tariff
 * @property-read NdcType $ndcType
 *
 * @method static TariffVoipNdcType findOne($condition)
 * @method static TariffVoipNdcType[] findAll($condition)
 */
class TariffVoipNdcType extends ActiveRecord
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

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {

            case 'ndc_type_id':
                if ($ndcType = NdcType::findOne(['id' => $value])) {
                    return $ndcType->getLink();
                }
                break;
        }

        return $value;
    }

    /**
     * Какие поля не показывать в исторических данных
     *
     * @param string $action
     * @return string[]
     */
    public static function getHistoryHiddenFields($action)
    {
        return [
            'id',
            'tariff_id',
        ];
    }

    /**
     * Вернуть parent_model_id для исторических данных
     *
     * @return int
     */
    public function getHistoryParentField()
    {
        return $this->tariff_id;
    }
}
