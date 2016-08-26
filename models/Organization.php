<?php
namespace app\models;

use Yii;
use ReflectionClass;
use app\exceptions\FormValidationException;
use yii\base\UnknownPropertyException;
use yii\db\ActiveRecord;
use app\queries\OrganizationQuery;
use yii\helpers\Url;
use app\classes\DynamicModel;
use app\classes\validators\ArrayValidator;
use app\dao\OrganizationDao;


/**
 * @property int $id
 * @property int organization_id                ID организации
 * @property string actual_from                 Дата с которой фирма начинает действовать
 * @property string actual_to                   Дата до которой фирма действует
 * @property int firma                          Ключ для связи с clients*
 * @property int country_id                     Код страны
 * @property string lang_code                   Код языка
 * @property int is_simple_tax_system         Упрощенная схема налогооблажения (1 - Да)
 * @property int vat_rate                       Ставка налога
 * @property string registration_id             Регистрационный номер (ОГРН)
 * @property string tax_registration_id         Идентификационный номер налогоплательщика (ИНН)
 * @property string tax_registration_reason     Код причины постановки (КПП)
 * @property string bank_account                Расчетный счет
 * @property string bank_name                   Название банка
 * @property string bank_correspondent_account  Кор. счет
 * @property string bank_bik                    БИК
 * @property string bank_swift                  SWIFT
 * @property string contact_phone               Телефон
 * @property string contact_fax                 Факс
 * @property string contact_email               E-mail
 * @property string contact_site                URL сайта
 * @property string logo_file_name              Название файла с логотипом
 * @property string stamp_file_name             Название файла с печатью
 * @property int director_id                    ID записи персон на должность директора
 * @property int accountant_id                  ID записи персон на должность бухгалтера
 *
 * @property Person director
 * @property Person accountant
 * @property
 */
class Organization extends ActiveRecord
{
    const MCN_TELEKOM = 1;
    const MCM_TELEKOM = 11;
    const TEL2TEL_KFT = 10;
    const TEL2TEL_LTD = 16;

    public static $ourLegalEntities = [
        self::MCN_TELEKOM => 'ООО "МСН Телеком"',
        self::MCM_TELEKOM => 'ООО "МСМ Телеком"',
        self::TEL2TEL_KFT => 'Tel2tel Kft.',
        self::TEL2TEL_LTD => 'Tel2Tel Ltd.',
    ];

    private $langCode = Language::LANGUAGE_DEFAULT;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'organization';
    }

    /**
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch(\Exception $e) {
            $i18n = $this->getI18N($this->langCode);
            if (array_key_exists($name, (array)$i18n)) {
                return $i18n[$name];
            }

            $i18n = $this->getI18N();
            if (array_key_exists($name, (array)$i18n)) {
                return $i18n[$name];
            }

            if (!$this->getPrimaryKey()) {
                return '';
            }
            else {
                throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
            }
        }
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\db\Exception
     */
    public function beforeSave($insert)
    {
        if (!(int)$this->organization_id) {
            $this->organization_id = $this->find()->max('organization_id') + 1;
        }

        $this->getDb()
            ->createCommand(
                "
                UPDATE `organization` SET
                    `actual_to` = :actual_to
                WHERE
                    `organization_id` = :id AND
                    `actual_from` < :date
                ORDER BY `actual_from` DESC
                LIMIT 1
                ", [
                    ':id' => $this->organization_id,
                    ':date' => $this->actual_from,
                    ':actual_to' => (new \DateTime($this->actual_from))->modify('-1 day')->format('Y-m-d')
                ]
            )
            ->execute();

        $next_record = $this
            ->find()
            ->where(['organization_id' => $this->organization_id])
            ->andWhere(['>', 'actual_from', $this->actual_from])
            ->orderBy('actual_from asc')
            ->one();
        if ($next_record instanceof Organization) {
            $this->actual_to = (new \DateTime($next_record->actual_from))->modify('-1 day')->format('Y-m-d');
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return OrganizationDao
     */
    public static function dao()
    {
        return OrganizationDao::me();
    }

    /**
     * @return OrganizationQuery
     */
    public static function find()
    {
        return new OrganizationQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDirector()
    {
        return $this->hasOne(Person::className(), ['id' => 'director_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountant()
    {
        return $this->hasOne(Person::className(), ['id' => 'accountant_id']);
    }

    /**
     * @return bool
     */
    public function isNotSimpleTaxSystem()
    {
        return !$this->is_simple_tax_system;
    }

    /**
     * @param string $langCode
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public function getI18N($langCode = Language::LANGUAGE_DEFAULT)
    {
        return
            $this->hasMany(OrganizationI18N::className(), ['organization_record_id' => 'id'])
                ->andWhere(['lang_code' => $langCode])
                ->indexBy('field')
                ->all();
    }

    /**
     * @param int $typeId
     * @return $this
     */
    public function getSettlementAccount($typeId = OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_RUSSIA)
    {
        $link = OrganizationSettlementAccount::findOne([
            'organization_record_id' => $this->id,
            'settlement_account_type_id' => $typeId,
        ]);

        return !is_null($link) ? $link : new OrganizationSettlementAccount;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @param string $langCode
     */
    public function setLanguage($langCode = Language::LANGUAGE_DEFAULT)
    {
        $this->langCode = $langCode;
        return $this;
    }

    /**
     * @param bool|true $runValidation
     * @param array $attributesName
     * @return bool
     * @throws \Exception
     */
    public function save($runValidation = true, $attributesName = [])
    {
        parent::save($runValidation, $attributesName);

        $organizationI18NModel = DynamicModel::validateData(
            Yii::$app->request->post((new ReflectionClass($this))->getShortName()),
            [
                [['name', 'legal_address', 'full_name', 'post_address'], ArrayValidator::className()],
            ]
        );

        if ($organizationI18NModel->hasErrors()) {
            throw new FormValidationException($organizationI18NModel);
        }

        foreach ($organizationI18NModel->attributes as $attribute => $i18nData) {
            foreach ($i18nData as $lang => $value) {
                $i18nTransaction = OrganizationI18N::getDb()->beginTransaction();
                try {
                    OrganizationI18N::deleteAll([
                        'organization_record_id' => $this->id,
                        'lang_code' => $lang,
                        'field' => $attribute,
                    ]);

                    $localization = new OrganizationI18N;
                    $localization->organization_record_id = $this->id;
                    $localization->lang_code = $lang;
                    $localization->field = $attribute;
                    $localization->value = $value;
                    $localization->save();

                    $i18nTransaction->commit();
                } catch (\Exception $e) {
                    $i18nTransaction->rollBack();
                    throw $e;
                }
            }
        }

        $organizationSettlementAccountModel = DynamicModel::validateData(
            Yii::$app->request->post((new ReflectionClass(OrganizationSettlementAccount::class))->getShortName()),
            [
                [
                    ['bank_name', 'bank_account', 'bank_bik', 'bank_correspondent_account', 'bank_address', 'bank_swift'],
                    ArrayValidator::className()
                ],
            ]
        );

        if ($organizationSettlementAccountModel->hasErrors()) {
            throw new FormValidationException($organizationSettlementAccountModel);
        }

        $settlementAccountData = [];
        foreach ($organizationSettlementAccountModel->attributes as $attribute => $data) {
            foreach ($data as $settlementAccountTypeId => $value) {
                if (!isset($settlementAccountData[$settlementAccountTypeId])) {
                    $settlementAccountData[$settlementAccountTypeId] = new OrganizationSettlementAccount;
                }
                $settlementAccountData[$settlementAccountTypeId]->{$attribute} = $value;
            }
        }

        foreach ($settlementAccountData as $settlementAccountTypeId => $settlementAccount) {
            /** @var OrganizationSettlementAccount $settlementAccount */
            $settlementAccountTransaction = OrganizationSettlementAccount::getDb()->beginTransaction();
            try {
                OrganizationSettlementAccount::deleteAll([
                    'organization_record_id' => $this->id,
                    'settlement_account_type_id' => $settlementAccountTypeId,
                ]);

                $settlementAccount->organization_record_id = $this->id;
                $settlementAccount->settlement_account_type_id = $settlementAccountTypeId;
                $settlementAccount->save();

                $settlementAccountTransaction->commit();
            } catch (\Exception $e) {
                $settlementAccountTransaction->rollBack();
                throw $e;
            }
        }

        return true;
    }

    /**
     * @return []
     */
    public function getOldModeInfo()
    {
        $director = $this->director;

        return [
            'name' => $this->name,
            'name_full' => $this->full_name,
            'address' => $this->legal_address,
            'post_address' => $this->post_address,
            'inn' => $this->tax_registration_id,
            'kpp' => $this->tax_registration_reason,
            'acc' => $this->settlementAccount->bank_account,
            'bank' => $this->settlementAccount->bank_name,
            'bank_name' => $this->settlementAccount->bank_name,
            'kor_acc' => $this->settlementAccount->bank_correspondent_account,
            'bik' => $this->settlementAccount->bank_bik,
            'phone' => $this->contact_phone,
            'fax' => $this->contact_fax,
            'email' => $this->contact_email,
            'director' => $director->name_nominative,
            'director_' => $director->name_genitive,
            'director_post' => $director->post_nominative,
            'director_post_' => $director->post_genitive,
            'firma' => $this->firma,
            'is_simple_tax_system' => $this->is_simple_tax_system,
            'logo' => str_replace('/images/', '', \Yii::$app->params['ORGANIZATION_LOGO_DIR']) . $this->logo_file_name,
            'site' => $this->contact_site,
            'src' => str_replace('/images/', '', \Yii::$app->params['STAMP_DIR']) . $this->stamp_file_name,
            'width' => 200,
            'height' => 200,
        ];
    }

    public function getOldModeDetail()
    {
        return
            $this->name . "<br /> Юридический адрес: " . $this->legal_address .
            (isset($this->post_address) ? "<br /> Почтовый адрес: " . $this->post_address : "") .
            "<br /> ИНН " . $this->tax_registration_id . ", КПП " . $this->tax_registration_reason .
            "<br /> Банковские реквизиты:<br /> р/с:&nbsp;" . $this->settlementAccount->bank_account . " в " . $this->settlementAccount->bank_name .
            "<br /> к/с:&nbsp;" . $this->settlementAccount->bank_correspondent_account . "<br /> БИК:&nbsp;" . $this->settlementAccount->bank_bik .
            "<br /> телефон: " . $this->contact_phone .
            (isset($this->contact_fax) && $this->contact_fax ? "<br /> факс: " . $this->contact_fax : "") .
            "<br /> е-mail: " . $this->contact_email;

    }

}
