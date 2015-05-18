<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\queries\ClientDocumentQuery;
use app\dao\ClientDocumentDao;

class ClientDocument extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_document';
    }

    public static function find()
    {
        return new ClientDocumentQuery(get_called_class());
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
        return ClientDocumentDao::me();
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
