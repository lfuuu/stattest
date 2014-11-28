<?php
namespace app\models;

use app\dao\ClientAccountDao;
use app\queries\ClientAccountQuery;
use yii\db\ActiveRecord;
use app\classes\behaviors\LogClientContractTypeChange;

/**
 * @property int $id
 * @property string $client
 * @property int $nds_zero
 * @property
 */
class ClientAccount extends ActiveRecord
{
    public static function tableName()
    {
        return 'clients';
    }

    public static function dao()
    {
        return ClientAccountDao::me();
    }

    public static function find()
    {
        return new ClientAccountQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            LogClientContractTypeChange::className()
            ];
    }

    public function getTaxRate()
    {
        return $this->nds_zero ? 0 : 0.18;
    }
}
