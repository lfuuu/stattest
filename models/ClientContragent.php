<?php
namespace app\models;

use app\classes\validators\InnKppValidator;
use yii\db\ActiveRecord;

class ClientContragent extends ActiveRecord
{
    const LEGAL_TYPE = 'legal';
    const PERSON_TYPE = 'person';
    const IP_TYPE = 'ip';

    public $cPerson = null;
    public $historyVersionDate = null;
    public $hasChecked;

    public static $taxRegtimeTypes = [
        '0' => 'Не определен',
        '1' => 'Полный (НДС 18%)',
        '2' => 'Без НДС',
    ];

    public static $legalTypes = [
        self::LEGAL_TYPE => 'Юр. лицо',
        self::PERSON_TYPE => 'ИП',
        self::IP_TYPE => 'Физю лицо',
    ];

    public static function tableName()
    {
        return 'client_contragent';
    }

    public function attributeLabels()
    {
        return [
            "name" => "Краткое наименование",
            "name_full" => "Полное наименование",
            "address_jur" => "Адрес юридический",
            "legal_type" => "Тип",
            "inn" => "ИНН",
            "inn_euro" => "ЕвроИНН",
            "kpp" => "КПП",
            "position" => "Должность Исполнительного органа",
            "fio" => "ФИО Исполнительного органа",
            "positionV" => "Должность Исполнительного органа",
            "fioV" => "ФИО Исполнительного органа",
            "tax_regime" => "Налоговый режим",
            "ogrn" => "Код ОГРН",
            "opf" => "Код ОПФ",
            "okpo" => "Код ОКПО",
            "okvd" => "Код ОКВЭД",
            'country_id' => 'Страна',
        ];
    }

    public function rules()
    {
        $rules = [
            [['inn', 'kpp'],  ['class' => InnKppValidator::className()]],
        ];
        return $rules;
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            return parent::save($runValidation, $attributeNames);
        }
        else {
            if (substr(php_sapi_name(), 0, 3) == 'cli' || !\Yii::$app->request->post('deferred-date') || \Yii::$app->request->post('deferred-date') === date('Y-m-d')) {
                return parent::save($runValidation, $attributeNames);
            } else {
                $behaviors = $this->behaviors;
                unset($behaviors['HistoryVersion']);
                $behaviors = array_keys($behaviors);
                foreach ($behaviors as $behavior)
                    $this->detachBehavior($behavior);
                $this->beforeSave(false);
            }
            return true;
        }
    }

    public function behaviors()
    {
        return [
            'HistoryVersion' => \app\classes\behaviors\HistoryVersion::className(),
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            'ContragentCountry' => \app\classes\behaviors\ContragentCountry::className(),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $super = ClientSuper::findOne($this->super_id);
        if($this->getOldAttribute('name') == $super->name) {
            $super->setAttribute('name', $this->name);
            $super->save();
        }

        foreach($this->getContracts() as $contact)
            foreach($contact->getAccounts() as $account)
                $account->sync1C();
    }

    public function beforeSave($insert)
    {
        if(!parent::beforeSave($insert))
            return false;

        if(!$this->name && !$this->name_full)
            $this->name = $this->name_full = 'Новый контрагент ';
        return true;
    }

    /**
     * @return array|ClientAccount[]
     */
    public function getAccounts()
    {
        $result = [];
        foreach($this->getContracts() as $contract){
            $result[] = array_merge($result,$contract->getAccounts());
        }
        return $result;
    }

    /**
     * @return ClientContragentPerson
     */
    public function getPerson()
    {
        $person = ClientContragentPerson::findOne(['contragent_id' => $this->id]);
        if($person)
            $person = $person->loadVersionOnDate($this->historyVersionDate);
        else {
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
        foreach($models as &$model){
            $model = $model->loadVersionOnDate($this->historyVersionDate);
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
     * @return $this
     */
    public function loadVersionOnDate($date)
    {
        return HistoryVersion::loadVersionOnDate($this, $date);
    }
}
