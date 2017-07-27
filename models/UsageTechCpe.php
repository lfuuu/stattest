<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\classes\transfer\TechCpeTransfer;
use app\dao\UsageTechCpeDao;
use app\helpers\usages\UsageTechCpeHelper;
use app\queries\UsageTechCpeQuery;

/**
 * @property int $id
 * @property UsageTechCpeHelper $helper
 */
class UsageTechCpe extends ActiveRecord
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

    /**
     * @return string
     */
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

    /**
     * @return TechCpeModel
     */
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
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    /**
     * @param $usage
     * @return TechCpeTransfer
     */
    public static function getTransferHelper($usage = null)
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