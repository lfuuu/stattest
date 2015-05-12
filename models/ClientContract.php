<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\queries\ClientContractQuery;
use app\dao\ClientContractDao;

class ClientContract extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contracts';
    }

    public static function find()
    {
        return new ClientContractQuery(get_called_class());
    }

    public function getAgreements()
    {
        return self::find()->account($this->client_id)->active()->agreement()->fromContract($this)->orderBy("contract_dop_date")->all();
    }

    public function getBlank()
    {
        return self::find()->account($this->client_id)->active()->blank()->fromContract($this)->last();
    }

    public static function dao()
    {
        return ClientContractDao::me();
    }

    public function getContent()
    {
        return self::dao()->getContent($this->client_id, $this->id);
    }

    public function erase()
    {
        @unlink(self::dao()->getFilePath($this->client_id, $this->id));

        return $this->delete();
    }
}
