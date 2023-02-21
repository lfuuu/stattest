<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Тип услуги (ВАТС, телефония, интернет и пр.)
 *
 * @property int $id
 * @property int $service_type_id
 * @property int $price_level_id
 * @property int $tariff_status_main_id
 * @property int $tariff_status_package_id
 *
 * @method static ServiceTypeFolder findOne($condition)
 * @method static ServiceTypeFolder[] findAll($condition)
 */
class ServiceTypeFolder extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_service_type_folder';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'service_type_id', 'price_level_id', 'tariff_status_main_id', 'tariff_status_package_id'], 'integer'],
            [['service_type_id', 'price_level_id', 'tariff_status_main_id'], 'required'],
        ];
    }
}
