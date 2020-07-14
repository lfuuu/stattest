<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;
use app\modules\nnp\models\NdcType;

/**
 * @property int $public_site_country_id
 * @property int $ndc_type_id
 *
 * @property-read NdcType $ndcType
 */
class PublicSiteNdcType extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'public_site_ndc_type';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNdcType()
    {
        return $this->hasOne(NdcType::class, ['id' => 'ndc_type_id']);
    }

}