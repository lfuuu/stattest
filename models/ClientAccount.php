<?php
namespace app\models;

use DateTimeZone;
use yii\db\ActiveRecord;
use app\dao\ClientAccountDao;
use app\queries\ClientAccountQuery;
use app\classes\FileManager;

/**
 * @property int $id
 * @property string $client
 * @property string $currency
 * @property string $nal
 * @property int $nds_zero
 * @property ClientSuper $superClient
 * @property ClientContractComment $lastComment
 * @property Country $country
 * @property Region $accountRegion
 * @property DateTimeZone $timezone
 * @property
 */
class ClientAccount extends ActiveRecord
{
    public $client_orig = '';

    public static $statuses = array(
        'negotiations' => array('name' => 'в стадии переговоров', 'color' => '#C4DF9B'),
        'testing' => array('name' => 'тестируемый', 'color' => '#6DCFF6'),
        'connecting' => array('name' => 'подключаемый', 'color' => '#F49AC1'),
        'work' => array('name' => 'включенный', 'color' => ''),
        'closed' => array('name' => 'отключенный', 'color' => '#FFFFCC'),
        'tech_deny' => array('name' => 'тех. отказ', 'color' => '#996666'),
        'telemarketing' => array('name' => 'телемаркетинг', 'color' => '#A0FFA0'),
        'income' => array('name' => 'входящие', 'color' => '#CCFFFF'),
        'deny' => array('name' => 'отказ', 'color' => '#A0A0A0'),
        'debt' => array('name' => 'отключен за долги', 'color' => '#C00000'),
        'double' => array('name' => 'дубликат', 'color' => '#60a0e0'),
        'trash' => array('name' => 'мусор', 'color' => '#a5e934'),
        'move' => array('name' => 'переезд', 'color' => '#f590f3'),
        'suspended' => array('name' => 'приостановленные', 'color' => '#C4a3C0'),
        'denial' => array('name' => 'отказ/задаток', 'color' => '#00C0C0'),
        'once' => array('name' => 'Интернет Магазин', 'color' => 'silver'),
        'reserved' => array('name' => 'резервирование канала', 'color' => 'silver'),
        'blocked' => array('name' => 'временно заблокирован', 'color' => 'silver'),
        'distr' => array('name' => 'Поставщик', 'color' => 'yellow'),
        'operator' => array('name' => 'Оператор', 'color' => 'lightblue')
    );

    private $_lastComment = false;
/*For old stat*/
    public function getType()
    {
        return $this->contract->contragent->legal_type;
    }

    public function getFirma()
    {
        return $this->contract->organization;
    }

    public function getManager()
    {
        return $this->contract->manager;
    }

    public function getManager_name()
    {
        return \app\models\User::find()->where(['user' => $this->contract->manager])->one()->name;;
    }

    public function getNumber()
    {
        return $this->contract->number;
    }

    public function getBusiness_process_id()
    {
        return $this->contract->business_process_id;
    }

    public function getBusiness_process_status_id()
    {
        return $this->contract->business_process_status_id;
    }

    public function getContract_type_id()
    {
        return $this->contract->contract_type_id;
    }

    public function getAccount_manager()
    {
        return $this->contract->account_manager;
    }

    public function getCompany()
    {
        return $this->contract->contragent->name;
    }

    public function getCompany_full()
    {
        return $this->contract->contragent->name_full;
    }

    public function getAddress_jur()
    {
        return $this->contract->contragent->address_jur;
    }

    public function getInn()
    {
        return $this->contract->contragent->inn;
    }

    public function getKpp()
    {
        return $this->contract->contragent->kpp;
    }

    public function getSigner_position()
    {
        return $this->contract->contragent->position;
    }

    public function getSigner_positionV()
    {
        return $this->contract->contragent->positionV;
    }

    public function getSigner_name()
    {
        return $this->contract->contragent->name;
    }

    public function getSigner_nameV()
    {
        return $this->contract->contragent->nameV;
    }

    public function getNds_zero()
    {
        return $this->contract->contragent->tax_regime == 'full' ? 0: 1;
    }

    public function getOgrn()
    {
        return $this->contract->contragent->ogrn;
    }

    public function getOkpo()
    {
        return $this->contract->contragent->Okpo;
    }

    public function getOpf()
    {
        return $this->contract->contragent->opf;
    }

    public function getOkvd()
    {
        return $this->contract->contragent->okvd;
    }
/**************/
    public static function tableName()
    {
        return 'clients';
    }

    public static function dao()
    {
        return ClientAccountDao::me();
    }

    public static function find()
    {
        return new ClientAccountQuery(get_called_class());
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            return parent::save($runValidation = true, $attributeNames = null);
        }
        else {
            if (substr(php_sapi_name(), 0, 3) == 'cli' || \Yii::$app->request->post('deferred-date') === date('Y-m-d')) {
                return parent::save($runValidation = true, $attributeNames = null);
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
            'LkWizardClean' => \app\classes\behaviors\LkWizardClean::className(),
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'Пароль',
            'comment' => 'Комментарий',
            //'status' => '   ',
            'usd_rate_percent' => 'USD уровень в процентах',
            'address_post' => 'Почтовый адрес',
            'address_post_real' => 'Действительный почтовый адрес',
            //'support' => '',
            //'login' => '',
            'bik' => 'БИК',
            'bank_properties' => 'Банковские реквизиты',
            'currency' => 'Валюта',
            //'currency_bill' => '',
            'stamp' => 'Печатать штамп',
            'nal' => 'Нал',
            //'telemarketing' => '',
            'sale_channel' => 'Канал продаж',
            //'uid' => '',
            //'site_req_no' => '',
            //'credit_USD' => '',
            //'credit_RUB' => '',
            //'credit' => '',
            'user_impersonate' => 'Наследовать права пользователя',
            'address_connect' => 'Предполагаемый адрес подключения',
            'phone_connect' => 'Предполагаемый телефон подключения',
            'id_all4net' => 'ID в All4Net',
            'dealer_comment' => 'Комментарий для дилера',
            'form_type' => 'Формирование с/ф',
            'metro_id' => 'Станция метро',
            'payment_comment' => 'Комментарии к платежу',
            'credit' => 'Разрешить кредит',
            'credit_size' => 'Размер кредита',
            //'previous_reincarnation' => '',
            //'cli_1c' => '',
            //'con_1c' => '',
            'corr_acc' => 'К/С',
            'pay_acc' => 'Р/С',
            'bank_name' => 'Название банка',
            'bank_city' => 'Город банка',
            //'sync_1c' => '',
            'price_type' => 'Тип цены',
            'voip_credit_limit' => 'Телефония, лимит использования (месяц)',
            'voip_disabled' => 'Выключить телефонию (МГ, МН, Местные мобильные)',
            'voip_credit_limit_day' => 'Телефония, лимит использования (день)',
            'balance' => 'Баланс',
            //'balance_usd' => '',
            'voip_is_day_calc' => 'Включить пересчет дневного лимита',
            'region' => 'Регион',
            //'last_account_date' => '',
            //'last_payed_voip_month' => '',
            'mail_print' => 'Печать конвертов',
            'mail_who' => '"Кому" письмо',
            'head_company' => 'Головная компания',
            'head_company_address_jur' => 'Юр. адрес головной компании',
            //'created' => '',
            'bill_rename1' => 'Номенклатура',
            //'nds_calc_method' => '',
            //'admin_contact_id' => '',
            //'admin_is_active' => '',
            'is_agent' => 'Агент',
            //'is_bill_only_contract' => '',
            //'is_bill_with_refund' => '',
            'is_with_consignee' => 'Использовать грузополучателя',
            'consignee' => 'Грузополучатель',
            'is_upd_without_sign' => 'Печать УПД без подписей',
            //'is_active' => '',
            'is_blocked' => 'Блокировка',
            //'is_closed' => '',
            'timezone_name' => 'Часовой пояс',
        ];
    }

    public function getTaxRate()
    {
        return $this->nds_zero ? 0 : 0.18;
    }

    public function getSuperClient()
    {
        return $this->hasOne(ClientSuper::className(), ['id' => 'super_id']);
    }


    public function getContract()
    {
        return $this->hasOne(ClientContract::className(), ['id' => 'contract_id']);
    }

    public function getContractType()
    {
        return $this->hasOne(ClientContractType::className(), ['id' => 'contract_type_id']);
    }

    public function getRegionName()
    {
        return $this->accountRegion ? $this->accountRegion->name : $this->region;
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    public function getAccountRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    public function getUserManager()
    {
        return $this->hasOne(User::className(), ["user" => "manager"]);
    }

    public function getUserAccountManager()
    {
        return $this->hasOne(User::className(), ["user" => "account_manager"]);
    }

    public function getLkWizardState()
    {
        return $this->hasOne(LkWizardState::className(), ["contract_id" => "id"]);
    }

    public function getStatusBP()
    {
        return $this->hasOne(ClientGridSettings::className(), ["id" => "business_process_status_id"]);
    }

    public function getContragent()
    {
        return $this->hasOne(ClientContragent::className(), ['id' => 'contragent_id']);
    }

    public function getFiles()
    {
        return $this->hasMany(ClientFile::className(), ['client_id' => 'id'])->orderBy("ts");
    }

    public function getFileManager()
    {
        return FileManager::create($this->id);
    }

    public function getStatusName()
    {
        return
            isset(self::$statuses[$this->status])
                ? self::$statuses[$this->status]['name']
                : $this->status;
    }

    public function getStatusColor()
    {
        return
            isset(self::$statuses[$this->status])
                ? self::$statuses[$this->status]['color']
                : '';
    }

    public function getLastComment()
    {
        if ($this->_lastComment === false) {
            $this->_lastComment =
                ClientContractComment::find()
                    ->andWhere(['contract_id' => $this->contract_id])
                    ->andWhere(['is_publish' => 1])
                    ->orderBy('ts desc')
                    ->all();
        }
        return $this->_lastComment;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimezone()
    {
        return new DateTimeZone($this->timezone_name);
    }

    public function getDefaultTaxId()
    {
        if ($this->nds_zero) {
            return TaxType::TAX_0;
        } else {
            return TaxType::TAX_18;
        }
    }

    public function getAllDocuments()
    {
        return $this->hasMany(ClientDocument::className(), ['client_id' => 'id']);
    }

    public function getAllContacts()
    {
        return $this->hasMany(ClientContact::className(), ['client_id' => 'id']);
    }

    public function getOfficialContact()
    {
        $res = [];
        $contacts = ClientContact::find(['client_id' => $this->id, 'is_official'=>1])->all;
        foreach($contacts as $contact){
            $res[$contact->type] = $contact;
        }
        return $res;
    }

    public function getBpStatuses()
    {
        $processes = [];
        foreach (ClientBP::find()->orderBy("sort")->all() as $b) {
            $processes[] = ["id" => $b->id, "up_id" => $b->client_contract_id, "name" => $b->name];
        }

        $statuses = [];
        foreach (ClientGridSettings::find()->select(["id", "name", "grid_business_process_id"])->where(["show_as_status" => 1])->orderBy("sort")->all() as $s) {
            $statuses[] = ["id" => $s->id, "name" => $s->name, "up_id" => $s->grid_business_process_id];
        }

        return ["processes" => $processes, "statuses" => $statuses];
    }
}
