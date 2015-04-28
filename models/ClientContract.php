<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\dao\ClientContractDao;

class ClientContract extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contracts';
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
