<?php
namespace app\models;

use Yii;
use app\classes\model\HistoryActiveRecord;
use app\classes\validators\InnKppValidator;

/**
 * Class ClientContragent
 * @property int id
 * @property int super_id
 * @property int country_id
 * @property string name
 * @property string legal_type
 * @property string name_full
 * @property string address_jur
 * @property string inn
 * @property string inn_euro
 * @property string kpp
 * @property string position
 * @property string fio
 * @property string positionV
 * @property string fioV
 * @property string signer_passport
 * @property string tax_regime
 * @property string opf_id
 * @property string okpo
 * @property string okvd
 * @property string ogrn
 * @property string comment
 * @property int sale_channel_id
 * @property int partner_contract_id
 * @property ClientContragentPerson person
 * @package app\models
 */
class ClientContragent extends HistoryActiveRecord
{
    const LEGAL_TYPE = 'legal';
    const PERSON_TYPE = 'person';
    const IP_TYPE = 'ip';

    const TAX_REGTIME_UNDEFINED = 'undefined';
    const TAX_REGTIME_OCH_VAT18 = 'OCH-VAT18';
    const TAX_REGTIME_YCH_VAT0 = 'YCH-VAT0';

    public $cPerson = null;
    public $historyVersionDate = null;
    public $hasChecked;

    public static $taxRegtimeTypes = [
        self::TAX_REGTIME_UNDEFINED => 'Не определен',
        self::TAX_REGTIME_OCH_VAT18 => 'Общая система налогообложения (ОСН)',
        self::TAX_REGTIME_YCH_VAT0 => 'Упрощенная система налогообложения (УСН)',
    ];

    public static $defaultOrganization = [
        self::LEGAL_TYPE => Organization::MCN_TELEKOM,
        self::PERSON_TYPE => Organization::MCM_TELEKOM,
        self::IP_TYPE => Organization::MCN_TELEKOM,
    ];

    public static function tableName()
    {
        return 'client_contragent';
    }

    public function rules()
    {
        $rules = [
            [['inn', 'kpp'], InnKppValidator::className()],
        ];
        return $rules;
    }

    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            'ContragentCountry' => \app\classes\behaviors\ContragentCountry::className(),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $super = ClientSuper::findOne($this->super_id);
        if ($this->getOldAttribute('name') == $super->name) {
            $super->setAttribute('name', $this->name);
            $super->save();
        }

        foreach ($this->getContracts() as $contact) {
            foreach ($contact->getAccounts() as $account) {
                $account->sync1C();
            }
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!$this->name && !$this->name_full) {

            $lang = Yii::$app->language;
            if ($this->country_id) {
                $country = Country::findOne(['code' => $this->country_id]);
                if ($country) {
                    $lang = $country->lang;
                }
            }

            $this->name = $this->name_full = Yii::t('contragent', 'New contragent', [], $lang);
        }
        return true;
    }

    /**
     * @return array|ClientAccount[]
     */
    public function getAccounts()
    {
        $result = [];
        foreach ($this->getContracts() as $contract) {
            $result = array_merge($result, $contract->getAccounts());
        }
        return $result;
    }

    /**
     * @return ClientContragentPerson
     */
    public function getPerson()
    {
        $person = ClientContragentPerson::findOne(['contragent_id' => $this->id]);
        if ($person) {
            if ($this->getHistoryVersionRequestedDate()) {
                $person = $person->loadVersionOnDate($this->getHistoryVersionRequestedDate());
            }
        } else {
            $person = new ClientContragentPerson();
            $person->contragent_id = $this->id;
        }
        return $person;
    }

    /**
     * @return array|ClientContract[]
     */
    public function getContracts()
    {
        $models = ClientContract::findAll(['contragent_id' => $this->id]);
        foreach ($models as &$model) {
            if ($model && $this->historyVersionRequestedDate) {
                $model->loadVersionOnDate($this->historyVersionRequestedDate);
            }
        }
        return $models;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    /**
     * @return ClientSuper
     */
    public function getSuper()
    {
        return $this->hasOne(ClientSuper::className(), ['id' => 'super_id']);
    }
}
