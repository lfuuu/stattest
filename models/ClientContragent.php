<?php
namespace app\models;

use app\classes\model\HistoryActiveRecord;
use app\classes\validators\InnKppValidator;

class ClientContragent extends HistoryActiveRecord
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

    public static function tableName()
    {
        return 'client_contragent';
    }

    public function rules()
    {
        $rules = [
            [['inn', 'kpp'], ['class' => InnKppValidator::className()]],
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

        foreach ($this->getContracts() as $contact)
            foreach ($contact->getAccounts() as $account)
                $account->sync1C();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert))
            return false;

        if (!$this->name && !$this->name_full)
            $this->name = $this->name_full = 'Новый контрагент ';
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
        if ($person){
            if($this->getHistoryVersionRequestedDate())
                $person = $person->loadVersionOnDate($this->getHistoryVersionRequestedDate());
        }
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
}
