<?php
namespace app\models;

use app\classes\BillContract;
use Yii;
use yii\db\ActiveRecord;
use app\queries\ClientDocumentQuery;
use app\dao\ClientDocumentDao;

class ClientDocument extends ActiveRecord
{
    const KEY = 'ZyG,GJr:/J4![%qhA,;^w^}HbZz;+9s34Y74cOf7[El)[A.qy5_+AR6ZUh=|W)z]y=*FoFs`,^%vt|6tM>E-OX5_Rkkno^T.';

    public $content = null,
        $group = '',
        $template = '';

    /**
     * @var ClientDocumentDao
     */
    private $dao;

    public static $types = [
        'contract' => 'Контракт',
        'agreement' => 'Дополнительное соглашение',
        'blank' => 'Бланк заказа'
    ];

    public static function tableName()
    {
        return 'client_document';
    }

    public function attributeLabels()
    {
        return [
            'contract_no' => '№',
            'contract_date' => 'От',
            'comment' => 'Комментарий'
        ];
    }

    public function rules()
    {
        return [
            ['contract_id', 'required'],
            [['contract_id', 'is_active', 'account_id'], 'integer'],
            [['contract_no', 'contract_date', 'contract_dop_date', 'comment', 'content', 'group', 'template'], 'string'],
            ['type', 'in', 'range' => array_keys(static::$types)],
            ['ts', 'default', 'value' => date('Y-m-d H-i-s')],
            ['is_active', 'default', 'value' => 1],
            ['user_id', 'default', 'value' => Yii::$app->user->id],
        ];
    }

    public static function find()
    {
        return new ClientDocumentQuery(get_called_class());
    }

    public function getAgreements()
    {
        return self::find()->andWhere(['contract_id' => $this->contract_id])->active()->agreement()->fromContract($this)->orderBy('contract_dop_date')->all();
    }

    public function getBlank()
    {
        return self::find()->andWhere(['account_id' => $this->account_id])->active()->blank()->fromContract($this)->last();
    }

    /**
     * @return ClientDocumentDao
     */
    public function dao($new = false)
    {
        if ($new || $this->dao === null)
            return new ClientDocumentDao([
                'model' => $this,
                'template' => $this->template,
                'group' => $this->group,
            ]);
        return $this->dao;
    }

    public function getFileContent()
    {
        return self::dao(true)->getContent();
    }

    public function erase()
    {
        self::dao()->delete();
        return $this->delete();
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getLink()
    {
        $data = $this->id . '-' . $this->contract_id;
        $d = substr(md5($data), 0, 1);
        if (($d < '0') || ($d > '9')) {
            $di = 10 + ord($d) - ord('a');
            if ($di >= 16) $di = 0;
        } else $di = ord($d) - ord('0');
        $data2 = "";
        $key = self::KEY;
        $l2 = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $v = (ord($data[$i]) + ord($key[($i + $di) % $l2])) % 256;
            $data2 .= chr($v);
        }
        return urlencode(base64_encode($data2) . $d);
    }

    public static function linkDecode($data)
    {
        $di = substr($data, strlen($data) - 1, 1);
        $data = substr($data, 0, strlen($data) - 1);
        if (($di < '0') || ($di > '9')) {
            $di = 10 + ord($di) - ord('a');
            if ($di >= 16) $di = 0;
        } else $di = ord($di) - ord('0');

        $data = base64_decode($data); //urldecode($data));
        $data2 = "";
        $key = self::KEY;
        $l2 = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) {
            $data2 .= chr((ord($data[$i]) + 256 - ord($key[($i + $di) % $l2])) % 256);
        }
        return $data2;
    }

    public function getAccount()
    {
        $params = $this->account_id ? $this->account_id : ['contract_id' => $this->contract_id];
        return ClientAccount::findOne($params)->loadVersionOnDate($this->contract_date);
    }

    public function getContract()
    {
        return ClientContract::findOne($this->contract_id)->loadVersionOnDate($this->contract_date);
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->contract_dop_date = '2012-01-01';
            $this->contract_dop_no = '0';
            if ($this->type != 'contract') {
                $utime = $this->type == 'blank' ? strtotime('2035-01-01') : ($this->contract_dop_date ? strtotime($this->contract_dop_date) : time());
                $lastContract = BillContract::getLastContract($this->contract_id, $utime);

                $this->contract_no = $this->contract_no ? $this->contract_no : ($lastContract ? $lastContract['no'] : 1);
                $this->contract_date = $this->contract_date ? $this->contract_date : date('Y-m-d', $lastContract ? $lastContract['date'] : time());
                $this->contract_dop_no = $this->contract_no;
                $this->contract_dop_date = ($this->type == 'agreement') ? $this->contract_date : date('Y-m-d');
            }

            if ($this->type == 'contract') {
                $oldContracts = self::findAll(['contract_id' => $this->contract_id]);
                if ($oldContracts)
                    foreach ($oldContracts as $oldContract)
                        $oldContract->erase();
            }
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && $this->group && $this->template) {
            $this->dao(true)->create();
        } elseif ($this->content !== null) {
            $this->dao(true)->setContent();
        }
        parent::afterSave($insert, $changedAttributes);
    }
}
