<?php

namespace app\models;

use app\dao\UsageTechCpeDao;
use app\queries\UsageTechCpeQuery;
use app\classes\model\ActiveRecord;
use app\helpers\usages\UsageTechCpeHelper;

/**
 * @property int $id
 * @property-read UsageTechCpeHelper $helper
 */
class UsageTechCpe extends ActiveRecord
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::class,
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
        return $this->hasOne(TechCpeModel::class, ['id' => 'id_model']);
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
        return $this->hasOne(ClientAccount::class, ['client' => 'client']);
    }

    /**
     * @return UsageTechCpeHelper
     */
    public function getHelper()
    {
        return new UsageTechCpeHelper($this);
    }

}