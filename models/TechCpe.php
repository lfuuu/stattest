<?php
namespace app\models;

use app\dao\TechCpeDao;
use yii\db\ActiveRecord;
use app\queries\TechCpeQuery;
use app\classes\transfer\TechCpeTransfer;

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
    public static function dao()
    {
        return TechCpeDao::me();
    }

    public function getTransferHelper()
    {
        return new TechCpeTransfer($this);
    }

    public static function getTypeTitle()
    {
        return 'Клиентские устройства';
    }

    public function getTypeDescription()
    {
        return $this->model->vendor . ' ' . $this->model->model;
    }

}