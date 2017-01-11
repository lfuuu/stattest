<?php
namespace app\models;

use app\classes\BillContract;
use app\classes\Encrypt;
use app\helpers\DateTimeZoneHelper;
use Yii;
use yii\db\ActiveRecord;
use app\queries\ClientDocumentQuery;
use app\dao\ClientDocumentDao;


/**
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

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_document';
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'contract_no' => '№',
            'contract_date' => 'Дата',
            'comment' => 'Комментарий',
            'user' => 'Кто добавил',
            'ts' => 'Когда',
            'is_external' => 'Внут/Внеш',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['contract_id', 'contract_no'], 'required'],
            [['contract_id', 'is_active', 'account_id', 'template_id'], 'integer', 'integerOnly' => true],
            [['contract_date', 'contract_dop_date', 'comment', 'content'], 'string'],
            ['type', 'in', 'range' => array_keys(static::$types)],
            ['is_external', 'in', 'range' => array_keys(ClientContract::$externalType)],
            ['ts', 'default', 'value' => date(DateTimeZoneHelper::DATETIME_FORMAT)],
            ['is_active', 'default', 'value' => 1],
            ['user_id', 'default', 'value' => Yii::$app->user->id],
        ];
    }

    /**
     * @return ClientDocumentQuery
     */
    public static function find()
    {
        return new ClientDocumentQuery(get_called_class());
    }

    /**
     * @return ClientDocument[]
     */
    public function getAgreements()
    {
        return self::find()
            ->andWhere(['contract_id' => $this->contract_id])
            ->active()
            ->agreement()
            ->fromContract($this)
            ->orderBy('contract_dop_date')
            ->all();
    }

    /**
     * @return ClientDocument
     */
    public function getBlank()
    {
        return self::find()
            ->andWhere(['account_id' => $this->account_id])
            ->active()
            ->blank()
            ->fromContract($this)
            ->last();
    }

    /**
     * @return ClientDocumentDao
     */
    public function dao()
    {
        return ClientDocumentDao::me();
    }

    /**
     * @return string
     */
    public function getFileContent()
    {
        return self::dao()->getFileContent($this);
    }

    /**
     * @return false|int
     * @throws \Exception
     */
    public function erase()
    {
        self::dao()->deleteFile($this);
        return $this->delete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return Encrypt::encodeString($this->id . '-' . $this->contract_id, 'CLIENTS');
    }

    /**
     * @param string $data
     * @return string
     */
    public static function linkDecode($data)
    {
        return Encrypt::decodeString($data, 'CLIENTS');
    }

    /**
     * @return ClientAccount
     */
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

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->contract_dop_date = '2012-01-01';
            $this->contract_dop_no = '0';
            if ($this->type != self::DOCUMENT_CONTRACT_TYPE) {
                $utime = $this->type == self::DOCUMENT_BLANK_TYPE ? strtotime('2035-01-01') : ($this->contract_dop_date ? strtotime($this->contract_dop_date) : time());
                $lastContract = BillContract::getLastContract($this->contract_id, $utime);

                $this->contract_no = $this->contract_no ? $this->contract_no : ($lastContract ? $lastContract['no'] : 1);
                $this->contract_date = $this->contract_date ? $this->contract_date : date(DateTimeZoneHelper::DATE_FORMAT,
                    $lastContract ? $lastContract['date'] : time());
                $this->contract_dop_no = $this->contract_no;
                $this->contract_dop_date = ($this->type == self::DOCUMENT_AGREEMENT_TYPE) ? $this->contract_date : date(DateTimeZoneHelper::DATE_FORMAT);
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

    /**
     * @param bool $insert
     * @param string[] $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && $this->template_id) {
            if ($this->type == self::DOCUMENT_CONTRACT_TYPE && $this->is_external == ClientContract::IS_EXTERNAL) {
            } else {
                $this->dao()->generateFile($this, $this->template_id);
            }

            if ($this->type == self::DOCUMENT_CONTRACT_TYPE) {
                $contract = $this->getContract();
                if ($contract->is_external != $this->is_external) {
                    $contract->is_external = $this->is_external;
                    $contract->save();
                }
            }
        } elseif ($this->content !== null) {
            $this->dao()->updateFile($this);
        }

        parent::afterSave($insert, $changedAttributes);
    }

}
