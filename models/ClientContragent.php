<?php

namespace app\models;

use app\classes\behaviors\ContragentCountry;
use app\classes\behaviors\EffectiveVATRate;
use app\classes\behaviors\SetTaxVoip;
use app\classes\model\HistoryActiveRecord;
use app\classes\validators\InnKppValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $super_id
 * @property int $country_id
 * @property string $lang_code
 * @property string $name
 * @property string $legal_type
 * @property string $name_full
 * @property string $address_jur
 * @property string $inn
 * @property string $inn_euro
 * @property string $kpp
 * @property string $branch_code
 * @property string $tax_registration_reason
 * @property string $position
 * @property string $fio
 * @property string $positionV
 * @property string $fioV
 * @property string $signer_passport
 * @property string $tax_regime
 * @property string $opf_id
 * @property string $okpo
 * @property string $okvd
 * @property string $ogrn
 * @property string $comment
 * @property int $sale_channel_id
 * @property int $partner_contract_id___DEPRICATED
 * @property string $created_at
 * @property int $is_lk_first
 * @property string $lk_status
 *
 * @property-read ClientAccount[] $accounts
 * @property-read ClientContragentPerson $person
 * @property-read ClientContragentPerson $personModel
 * @property-read ClientContract[] $contracts
 * @property-read ClientContract[] $contractsActiveQuery
 * @property-read Country $country
 * @property-read ClientSuper $super
 * @property-read ClientContact $partnerContract
 */
class ClientContragent extends HistoryActiveRecord
{
    const LEGAL_TYPE = 'legal';
    const PERSON_TYPE = 'person';
    const IP_TYPE = 'ip';

    const TAX_REGTIME_UNDEFINED = 'undefined';
    const TAX_REGTIME_OCH_VAT18 = 'OCH-VAT18';
    const TAX_REGTIME_YCH_VAT0 = 'YCH-VAT0';

    public $hasChecked = false;
    public $isSimpleValidation = false; // валидация для wizarda

    public static $taxRegtimeTypes = [
        self::TAX_REGTIME_UNDEFINED => 'Не определен',
        self::TAX_REGTIME_OCH_VAT18 => 'Общая система налогообложения (ОСН)',
        self::TAX_REGTIME_YCH_VAT0 => 'Упрощенная система налогообложения (УСН)',
    ];

    public static $names = [
        self::LEGAL_TYPE => 'Юр. лицо',
        self::PERSON_TYPE => 'Физ. лицо',
        self::IP_TYPE => 'ИП'
    ];

    public static $defaultOrganization = [
        self::LEGAL_TYPE => Organization::MCN_TELECOM,
        self::PERSON_TYPE => Organization::MCN_TELECOM_SERVICE,
        self::IP_TYPE => Organization::MCN_TELECOM,
    ];

    public
        $attributesProtectedForVersioning = [
        'super_id',
        'country_id',
        'lang_code',
        'comment',
        'sale_channel_id',
        'partner_contract_id',
        'branch_code',
    ];


    /**
     * Возвращает название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_contragent';
    }

    /**
     * Правила
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['country_id', 'required'],
            ['country_id', 'integer'],
            [['inn', 'kpp'], InnKppValidator::class],
        ];
        return $rules;
    }

    /**
     * Поведение
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'ContragentCountry' => ContragentCountry::class,
            'EffectiveVATRate' => EffectiveVATRate::class,
            'SetTaxVoip' => SetTaxVoip::class,
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value' => new Expression('UTC_TIMESTAMP()'), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
        ];
    }

    /**
     * Название полей
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'inn' => 'ИНН',
            'inn_euro' => 'ЕвроИНН',
            'kpp' => 'КПП',
            'branch_code' => 'Код филиала',
            'name' => 'Название',
            'name_full' => 'Название полное',
            'address_jur' => 'Юридический адрес',
            'legal_type' => 'Юридический тип',
            'fio' => 'ФИО Исполнительного органа',
            'comment' => 'Комментарий',
            'okpo' => 'Код ОКПО',
            'okvd' => 'Код ОКВЭД',
            'ogrn' => 'Код ОГРН',
            'opf_id' => 'Код ОПФ',
            'country_id' => 'Страна',
            'lang_code' => 'Язык',
            'tax_regime' => 'Налоговый режим',
            'position' => 'Должность Исполнительного органа',
            'sale_channel_id' => 'Откуда узнали о нас',
            'partner_contract_id' => 'Партнер',
            'super_id' => 'Супер-клиент',
            'is_lk_first' => 'Основные данные в ЛК',
            'lk_status' => 'Статус верификации в ЛК',
        ];
    }

    /**
     * Model afterSave
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
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

    /**
     * Model BeforeSave
     *
     * @param bool $insert
     * @return bool
     */
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
     * Вернуть ClientAccount
     *
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
     * Вернуть ClientContragentPerson
     *
     * @return ClientContragentPerson
     */
    public function getPerson()
    {
        $person = ClientContragentPerson::findOne(['contragent_id' => $this->id]);
        if ($person) {
            if ($historyDate = $this->getHistoryVersionRequestedDate()) {
                $person = $person->loadVersionOnDate($historyDate);
            }
        } else {
            $person = new ClientContragentPerson();
            $person->contragent_id = $this->id;
        }

        return $person;
    }

    /**
     * @return ActiveQuery
     */
    public function getPersonModel()
    {
        return $this->hasOne(ClientContragentPerson::class, ['contragent_id' => 'id']);

    }

    /**
     * Вернуть ClientContract
     *
     * @return array|ClientContract[]
     */
    public function getContracts()
    {
        $models = ClientContract::findAll(['contragent_id' => $this->id]);
        foreach ($models as &$model) {
            if ($model && $this->getHistoryVersionRequestedDate()) {
                $model->loadVersionOnDate($this->getHistoryVersionRequestedDate());
            }
        }

        return $models;
    }

    /**
     * @return ActiveQuery
     */
    public function getContractsActiveQuery()
    {
        return $this->hasMany(ClientContract::class, ['contragent_id' => 'id']);
    }

    /**
     * Вернуть Country
     *
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_id']);
    }

    /**
     * Вернуть ClientSuper
     *
     * @return ActiveQuery
     */
    public function getSuper()
    {
        return $this->hasOne(ClientSuper::class, ['id' => 'super_id']);
    }

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {
            case 'super_id':
                if ($clientSuper = ClientSuper::findOne(['id' => $value])) {
                    return $clientSuper->name;
                }
                break;
            case 'partner_contract_id':
                if ($clientContact = ClientContact::findOne(['id' => $value])) {
                    return $clientContact->data;
                }
                break;
            case 'opf_id':
                if (!$value) {
                    return '';
                }

                if ($codeOpf = CodeOpf::findOne(['id' => $value])) {
                    return $codeOpf->name;
                }
                break;
            case 'country_id':
                if ($country = Country::findOne(['code' => $value])) {
                    return $country->getLink();
                }
                break;
            case 'sale_channel_id':
                if (!$value) {
                    return '';
                }

                $saleChannelList = SaleChannel::getList();
                if (isset($saleChannelList[$value])) {
                    return $saleChannelList[$value];
                }
                break;
        }
        return $value;
    }

    /**
     * Какие поля не показывать в исторических данных
     *
     * @param string $action
     * @return string[]
     */
    public static function getHistoryHiddenFields($action)
    {
        return ['id'];
    }
}
