<?php
namespace app\models;

use app\classes\BillContract;
use app\classes\Encrypt;
use Yii;
use yii\db\ActiveRecord;
use app\queries\ClientDocumentQuery;
use app\dao\ClientDocumentDao;


/**
 * Class ClientDocument
 *
 * @property int id
 * @property int contract_id
 * @property int account_id
 * @property string contract_no
 * @property string contract_date
 * @property string contract_dop_date
 * @property int contract_dop_no
 * @property int user_id
 * @property string ts
 * @property string comment
 * @property int is_active
 * @property string type
 * @property string fileContent
 * @package app\models
 */
class ClientDocument extends ActiveRecord
{
    const DOCUMENT_BLANK_TYPE = 'blank';
    const DOCUMENT_CONTRACT_TYPE = 'contract';
    const DOCUMENT_AGREEMENT_TYPE = 'agreement';

    public $content;
    public $template_id;
    public $is_external;

    public static $types = [
        'contract' => 'Контракт',
        'agreement' => 'Дополнительное соглашение',
        'blank' => 'Бланк заказа',
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
            [['contract_id', 'contract_no'], 'required'],
            [['contract_id', 'is_active', 'account_id', 'template_id'], 'integer', 'integerOnly' => true],
            [['contract_date', 'contract_dop_date', 'comment', 'content'], 'string'],
            ['type', 'in', 'range' => array_keys(static::$types)],
            ['is_external', 'in', 'range' => array_keys(ClientContract::$externalType)],
            ['ts', 'default', 'value' => date('Y-m-d H:i:s')],
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

    public function dao()
    {
        return ClientDocumentDao::me();
    }

    public function getFileContent()
    {
        return self::dao()->getFileContent($this);
    }

    public function erase()
    {
        self::dao()->deleteFile($this);
        return $this->delete();
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getLink()
    {
        return Encrypt::encodeString($this->id . '-' . $this->contract_id, 'CLIENTS');
    }

    public static function linkDecode($data)
    {
        return Encrypt::decodeString($data, 'CLIENTS');
    }

    public function getAccount()
    {
        $params = $this->account_id ? $this->account_id : ['contract_id' => $this->contract_id];
        return ClientAccount::findOne($params)->loadVersionOnDate($this->contract_date);
    }

    /**
     * @return ClientContract
     */
    public function getContract()
    {
        return ClientContract::findOne($this->contract_id)->loadVersionOnDate($this->contract_date);
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->contract_dop_date = '2012-01-01';
            $this->contract_dop_no = '0';
            if ($this->type != self::DOCUMENT_CONTRACT_TYPE) {
                $utime = $this->type == self::DOCUMENT_BLANK_TYPE ? strtotime('2035-01-01') : ($this->contract_dop_date ? strtotime($this->contract_dop_date) : time());
                $lastContract = BillContract::getLastContract($this->contract_id, $utime);

                $this->contract_no = $this->contract_no ? $this->contract_no : ($lastContract ? $lastContract['no'] : 1);
                $this->contract_date = $this->contract_date ? $this->contract_date : date('Y-m-d',
                    $lastContract ? $lastContract['date'] : time());
                $this->contract_dop_no = $this->contract_no;
                $this->contract_dop_date = ($this->type == self::DOCUMENT_AGREEMENT_TYPE) ? $this->contract_date : date('Y-m-d');
            }

            if ($this->type == self::DOCUMENT_CONTRACT_TYPE) {
                $oldContracts = self::findAll(['contract_id' => $this->contract_id]);
                if ($oldContracts) {
                    foreach ($oldContracts as $oldContract) {
                        $oldContract->erase();
                    }
                }
            }
        }

        if ($this->type == self::DOCUMENT_CONTRACT_TYPE) {
            $contract = ClientContract::findOne($this->contract_id);
            $contract->number = $this->contract_no;
            $contract->save();
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && $this->template_id) {
            if ($this->type == self::DOCUMENT_CONTRACT_TYPE && $this->is_external == ClientContract::IS_EXTERNAL) {
            } else {
                $this->dao()->generateFile($this, $this->template_id);
            }

            $contract = $this->getContract();
            if ($contract->is_external != $this->is_external) {
                $contract->is_external = $this->is_external;
                $contract->save();
            }
        } elseif ($this->content !== null) {
            $this->dao()->updateFile($this);
        }

        return parent::afterSave($insert, $changedAttributes);
    }
}
