<?php
namespace app\models;

use app\dao\TechCpeDao;
use yii\db\ActiveRecord;
use app\queries\TechCpeQuery;

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

    /**
     * @return TechCpeQuery
     */
    public static function find()
    {
        return new TechCpeQuery(get_called_class());
    }

    public function getModel()
    {
        return $this->hasOne(TechCpeModel::className(), ['id' => 'id_model']);
    }

    /**
     * @return TechCpeDao
     */
    public function dao()
    {
        return TechCpeDao::me();
    }

}