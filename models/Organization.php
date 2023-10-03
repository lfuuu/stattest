<?php

namespace app\models;

use app\classes\DynamicModel;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\classes\traits\I18NGetTrait;
use app\classes\validators\ArrayValidator;
use app\dao\OrganizationDao;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\queries\OrganizationQuery;
use DateTime;
use DateTimeZone;
use ReflectionClass;
use Yii;
use yii\db\Expression;
use yii\helpers\Url;


/**
 * @property int $id
 * @property int $organization_id                ID организации
 * @property string $actual_from                 Дата с которой фирма начинает действовать
 * @property string $actual_to                   Дата до которой фирма действует
 * @property int $firma                          Ключ для связи с clients*
 * @property string $name                        Наименование (виртуальное свойство, зависит от I18N)
 * @property string $full_name                   Полное наименование (виртуальное свойство, зависит от I18N)
 * @property string $legal_address               Юр. адрес (виртуальное свойство, зависит от I18N)
 * @property string $post_address                Почтовый адрес (виртуальное свойство, зависит от I18N)
 * @property int $country_id                     Код страны
 * @property string $lang_code                   Код языка
 * @property int $is_simple_tax_system           Упрощенная схема налогооблажения (1 - Да)
 * @property int $vat_rate                       Ставка налога
 * @property string $registration_id             Регистрационный номер (ОГРН)
 * @property string $tax_registration_id         Идентификационный номер налогоплательщика (ИНН)
 * @property string $tax_registration_reason     Код причины постановки (КПП)
 * @property string $contact_phone               Телефон
 * @property string $contact_fax                 Факс
 * @property string $contact_email               E-mail
 * @property string $contact_site                URL сайта
 * @property string $logo_file_name              Название файла с логотипом
 * @property string $stamp_file_name             Название файла с печатью
 * @property int $director_id                    ID записи персон на должность директора
 * @property int $accountant_id                  ID записи персон на должность бухгалтера
 * @property int $invoice_counter_range_id       Счетчик с/ф месячный / годовой и т.д.
 *
 * @property-read Person $director
 * @property-read Person $accountant
 * @property-read Organization $actual
 * @property-read OrganizationSettlementAccount $settlementAccount
 */
class Organization extends ActiveRecord
{
    use I18NGetTrait;

    const MCN_TELECOM = 1;
    const MCN_TELECOM_RETAIL = 11; // ex "MCM Telecom"
    const MCN_TELECOM_SERVICE = 21; // МСН Телеком Сервис
    const TEL2TEL_KFT = 10; // Для некоторых стран это переводится как "TEL2TEL_LTD", но не надо это путать с id=16
    const TEL2TEL_LTD = 16;
    const INTERNAL_OFFICE = 18; // Взаиморасчеты MCN - Внутренний офис
    const MCN_TELECOM_KFT_SK = 19; // MCNtelecom Kft. Словакия
    const TEL2TEL_GMBH = 20;
    const WL_MCN_INNONET = 23;
    const AB_SERVICE_MARCOMNET = 14;
    const WELLSTART = 8;

    const INVOICE_COUNTER_RANGE_ID_MONTH = 1;
    const INVOICE_COUNTER_RANGE_ID_YEAR = 2;

    public static $invoiceCounterRangeNames = [
        self::INVOICE_COUNTER_RANGE_ID_MONTH => 'Месяц',
        self::INVOICE_COUNTER_RANGE_ID_YEAR => 'Год',
    ];

    public static $ourLegalEntities = [
        self::MCN_TELECOM => 'МСН Телеком',
        self::MCN_TELECOM_RETAIL => 'МСН Телеком Ритейл',
        self::TEL2TEL_GMBH => 'MCNTelecom GmbH',
        self::TEL2TEL_KFT => 'MCNTelecom Kft.',
        self::TEL2TEL_LTD => 'MCNTelecom Ltd.',
        self::WELLSTART => 'ООО «Веллстарт»',
    ];

    private $langCode = Language::LANGUAGE_DEFAULT;

    // Виртуальные поля для локализации
    private static $_virtualPropertiesI18N = [
        'name',
        'legal_address',
        'full_name',
        'post_address',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'organization';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }


    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\db\Exception
     */
    public function beforeSave($insert)
    {
        if (!(int)$this->organization_id) {
            $this->organization_id = self::find()->max('organization_id') + 1;
        }

        self::getDb()
            ->createCommand('
                UPDATE organization SET
                    actual_to = :actual_to
                WHERE
                    organization_id = :id AND
                    actual_from < :date
                ORDER BY actual_from DESC
                LIMIT 1
            ', [
                ':id' => $this->organization_id,
                ':date' => $this->actual_from,
                ':actual_to' => (new \DateTime($this->actual_from))
                    ->modify('-1 day')
                    ->format(DateTimeZoneHelper::DATE_FORMAT)
            ])
            ->execute();

        $next_record = self::find()
            ->where(['organization_id' => $this->organization_id])
            ->andWhere(['>', 'actual_from', $this->actual_from])
            ->orderBy('actual_from asc')
            ->one();

        if ($next_record instanceof Organization) {
            $this->actual_to = (new \DateTime($next_record->actual_from))
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return OrganizationDao
     * @throws \yii\base\Exception
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
        return $this->hasOne(Country::class, ['code' => 'country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDirector()
    {
        return $this->hasOne(Person::class, ['id' => 'director_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountant()
    {
        return $this->hasOne(Person::class, ['id' => 'accountant_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
     */
    public function getActual()
    {
        $actualDate =
            (new DateTime())
                ->setTimezone(new DateTimeZone('UTC'))
                ->format(DateTimeZoneHelper::DATE_FORMAT);

        return $this->hasOne(Organization::class, ['organization_id' => 'organization_id'])
            ->from(['o1' => Organization::tableName()])
            ->where(
                new Expression(
                    'o1.id = (' .
                    Organization::find()
                        ->select(['MAX(o2.id)'])
                        ->from(['o2' => Organization::tableName()])
                        ->where(['o2.organization_id' => new Expression('o1.organization_id')])
                        ->andWhere(['o2.actual_from' => new Expression('o1.actual_from')])
                        ->andWhere(new Expression('CAST(:date AS date) BETWEEN o1.actual_from AND o2.actual_to', ['date' => $actualDate]))
                        ->orderBy('o2.`actual_from` DESC')
                        ->limit(1)
                        ->createCommand()->rawSql .
                    ')'
                )
            );
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
     * @return array
     */
    public function getI18N($langCode = Language::LANGUAGE_DEFAULT)
    {
        return
            $this->hasMany(OrganizationI18N::class, ['organization_record_id' => 'id'])
                ->andWhere(['lang_code' => $langCode])
                ->indexBy('field')
                ->all();
    }

    /**
     * @param int $typeId
     * @return OrganizationSettlementAccount|null|static
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
     * @return $this
     */
    public function setLanguage($langCode = Language::LANGUAGE_DEFAULT)
    {
        $this->langCode = $langCode;
        return $this;
    }

    /**
     * @param bool|true $runValidation
     * @param null|[] $attributesName
     * @return bool
     * @throws \Exception
     */
    public function save($runValidation = true, $attributesName = null)
    {
        if (!parent::save($runValidation, $attributesName)) {
            return false;
        }

        // Получение данных локализации
        $organizationI18NModel = DynamicModel::validateData(
            Yii::$app->request->post((new ReflectionClass($this))->getShortName()),
            [
                [self::$_virtualPropertiesI18N, ArrayValidator::class],
            ]
        );

        if ($organizationI18NModel->hasErrors()) {
            throw new ModelValidationException($organizationI18NModel);
        }

        foreach ($organizationI18NModel->attributes as $attribute => $i18nData) {
            foreach ($i18nData as $lang => $value) {
                $i18nTransaction = OrganizationI18N::getDb()->beginTransaction();
                try {
                    $localization = OrganizationI18N::findOne([
                        'organization_record_id' => $this->id,
                        'lang_code' => $lang,
                        'field' => $attribute,
                    ]);
                    if (!$localization) {
                        $localization = new OrganizationI18N;
                        $localization->organization_record_id = $this->id;
                        $localization->lang_code = $lang;
                        $localization->field = $attribute;
                    }
                    $localization->value = $value;
                    $localization->save();

                    $i18nTransaction->commit();
                } catch (\Exception $e) {
                    $i18nTransaction->rollBack();
                    throw $e;
                }
            }
        }

        // Получение данных о платежных реквизитах
        $organizationSettlementAccountModel = DynamicModel::validateData(
            Yii::$app->request->post((new ReflectionClass(OrganizationSettlementAccount::class))->getShortName()),
            [
                [
                    ['bank_name', 'bank_bik', 'bank_correspondent_account', 'bank_address', 'bank_swift'],
                    ArrayValidator::class
                ],
            ]
        );

        if ($organizationSettlementAccountModel->hasErrors()) {
            throw new ModelValidationException($organizationSettlementAccountModel);
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

        // Получение данных о платежных реквизитах зависящих от валюты
        $settlementAccountPropertiesData = Yii::$app->request->post((new ReflectionClass(OrganizationSettlementAccountProperties::class))->getShortName());

        foreach ($settlementAccountPropertiesData as $propertyName => $values) {
            foreach ($values as $settlementAccountTypeId => $value) {
                $property = OrganizationSettlementAccountProperties::findOne([
                    'organization_record_id' => $this->id,
                    'settlement_account_type_id' => $settlementAccountTypeId,
                    'property' => $propertyName,
                ]);

                if (is_null($property)) {
                    $property = new OrganizationSettlementAccountProperties;
                }

                $settlementAccountPropertiesTransaction = OrganizationSettlementAccountProperties::getDb()->beginTransaction();
                try {
                    $property->organization_record_id = $this->id;
                    $property->settlement_account_type_id = $settlementAccountTypeId;
                    $property->property = $propertyName;
                    $property->value = $value;
                    $property->save();

                    $settlementAccountPropertiesTransaction->commit();
                } catch (\Exception $e) {
                    $settlementAccountPropertiesTransaction->rollBack();
                    throw $e;
                }
            }
        }

        return true;
    }

    /**
     * @return array
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

    /**
     * @return string
     */
    public function getOldModeDetail()
    {
        return
            $this->name . '<br /> Юридический адрес: ' . $this->legal_address .
            (isset($this->post_address) ? '<br /> Почтовый адрес: ' . $this->post_address : '') .
            '<br /> ИНН ' . $this->tax_registration_id . ', КПП ' . $this->tax_registration_reason .
            '<br /> Банковские реквизиты:<br /> р/с:&nbsp;' . $this->settlementAccount->bank_account . ' в ' . $this->settlementAccount->bank_name .
            '<br /> к/с:&nbsp;' . $this->settlementAccount->bank_correspondent_account . '<br /> БИК:&nbsp;' . $this->settlementAccount->bank_bik .
            '<br /> телефон: ' . $this->contact_phone .
            (isset($this->contact_fax) && $this->contact_fax ? '<br /> факс: ' . $this->contact_fax : '') .
            '<br /> е-mail: ' . $this->contact_email;

    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/organization/edit/', 'id' => $id]);
    }

    /**
     * Вернуть html: имя + ссылка
     *
     * @return string
     */
    public function getLink()
    {
        return Html::a(Html::encode($this->name), $this->getUrl());
    }

    /**
     * Является ли организация "MCN Telecom Kft."
     *
     * @param int $organizationId
     * @return bool
     */
    public static function isMcnTeleсomKft($organizationId)
    {
        return in_array($organizationId, [self::TEL2TEL_KFT, self::MCN_TELECOM_KFT_SK]);
    }
}
