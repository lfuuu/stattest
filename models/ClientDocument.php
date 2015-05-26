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

    public function getUserName()
    {
        $r = $this->hasOne(User::className(), ['id' => 'user_id'])->one();
        return $r->name;
    }

    public function getLink()
    {
        $data = $this->id . '-' . $this->client_id;
        $d = substr(md5($data), 0, 1);
        if (($d < '0') || ($d > '9')) {
            $di = 10 + ord($d) - ord('a');
            if ($di >= 16) $di = 0;
        } else $di = ord($d) - ord('0');
        $data2 = "";
        $key = 'ZyG,GJr:/J4![%qhA,;^w^}HbZz;+9s34Y74cOf7[El)[A.qy5_+AR6ZUh=|W)z]y=*FoFs`,^%vt|6tM>E-OX5_Rkkno^T.';
        $l2 = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $v = (ord($data[$i]) + ord($key[($i + $di) % $l2])) % 256;
            $data2 .= chr($v);
        }
        return urlencode(base64_encode($data2) . $d);

    }
}
