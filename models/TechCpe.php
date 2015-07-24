<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TechCpe extends ActiveRecord
{
    public static function tableName()
    {
        return 'tech_cpe';
    }

    public function getModel()
    {
        return $this->hasOne(TechCpeModel::className(), ['id' => 'id_model']);
    }
}