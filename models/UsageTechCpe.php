<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\UsageTechCpeDao;
use app\queries\UsageTechCpeQuery;
use app\classes\transfer\TechCpeTransfer;
use app\helpers\usages\UsageTechCpeHelper;

/**
 * @property int $id
 * @property
 */
class UsageTechCpe extends ActiveRecord
{

    public static function tableName()
    {
        return 'usage_tech_cpe';
    }

    /**
     * @return UsageTechCpeQuery
     */
    public static function find()
    {
        return new UsageTechCpeQuery(get_called_class());
    }

    public function getModel()
    {
        return $this->hasOne(TechCpeModel::className(), ['id' => 'id_model']);
    }

    /**
     * @return UsageTechCpeDao
     */
    public static function dao()
    {
        return UsageTechCpeDao::me();
    }

    /**
     * @param $usage
     * @return TechCpeTransfer
     */
    public static function getTransferHelper($usage)
    {
        return new TechCpeTransfer($usage);
    }

    /**
     * @return UsageTechCpeHelper
     */
    public function getHelper()
    {
        return new UsageTechCpeHelper($this);
    }

}