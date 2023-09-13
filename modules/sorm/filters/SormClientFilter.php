<?php

namespace app\models\filter;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\ClientContragent;
use app\models\ClientContragentPerson;
use app\models\EquipmentUser;
use app\models\Region;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Фильтрация для SormClient
 */
class SormClientFilter extends ClientAccount
{
    const FILTER_NONE = '';
    const FILTER_NOT_UPLOADED = 'not_uploaded';
    const FILTER_UPLOADED = 'uploaded';
    const FILTER_WITH_ERRORS = 'errors';
    const FILTER_ACTIVE = 'active';

    public static $filterList = [
        self::FILTER_NONE => '-- Все --',
        self::FILTER_NOT_UPLOADED => 'Не выгружаемые сейчас',
        self::FILTER_UPLOADED => 'Только выгружаемые',
        self::FILTER_ACTIVE => 'ЛС с активными услугами',
    ];

    const ERR_NO = '';
    const ERR_WO_EQUSERS = 'without_equser';
    const ERR_ALL = 'all';

    public static $errList = [
        self::ERR_NO => ' --- ',
        self::ERR_WO_EQUSERS => 'Да, без польз. оборуд.',
        self::ERR_ALL => 'Все',
    ];

    public $region_id = '';
    public $account_manager = '';
    public $filter_by = '';
    public $is_with_error = '';

    const REGION_ID_HABAROVSK = 83;
    const REGION_ID_KRASNOIARSK = 55;
    const REGION_ID_IZHEVSK = 74;
    const REGION_ID_TOMSK = 54;
    const REGION_ID_SARANSK = 41;
    const REGION_ID_MSK = 99;
    const REGION_ID_CHBOKSARY = 62;
    const REGION_ID_KAZAN = 93;
    const REGION_ID_BARNAUL = 50;
    const REGION_ID_EKATERINBURG = 95;
    const REGION_ID_VLADIMIR = 42;
    const REGION_ID_KOSTROMA = 46;
    const REGION_ID_NOVOSIBIRSK = 94;
    const REGION_ID_VOLGOGRAD = 91;
    const REGION_ID_ULIANOVSK = 63;
    const REGION_ID_BLAGOVESHCHENSK = 37;
    const REGION_ID_YAKUTSK = 36;
    const REGION_ID_VLADIVOSTOK = 89;
    const REGION_ID_CHELABINSK = 90;
    const REGION_ID_SARATOV = 66;
    const REGION_ID_PSKOV = 34;
    const REGION_ID_ULANUDE = 31;
    const REGION_ID_SPB = 98;
    const REGION_ID_KIROV = 69;
    const REGION_ID_KURSK = 58;
    const REGION_ID_STAVROPOL = 73;
    const REGION_ID_KALININGRAD = 72;
    const REGION_ID_VORONEJ = 86;
    const REGION_ID_YAROSLAVL = 56;
    const REGION_ID_TAMBOV = 43;
    const REGION_ID_ARHANGELSK = 35;
    const REGION_ID_KEMEROVO = 52;
    const REGION_ID_PENZA = 65;
    const REGION_ID_VOLOGDA = 32;
    const REGION_ID_PETROZAVODSK = 33;
    const REGION_ID_KALUGA = 47;
    const REGION_ID_OMSK = 53;
    const REGION_ID_TUMEN = 78;
    const REGION_ID_BELGOROD = 49;
    const REGION_ID_LIPETSK = 59;
    const REGION_ID_SURGUT = 68;
    const REGION_ID_BRANSK = 85;
    const REGION_ID_IRKUTSK = 51;
    const REGION_ID_SYKTYVKAR = 29;
    const REGION_ID_KRASNODAR = 97;
    const REGION_ID_NN = 88;
    const REGION_ID_SMOLENSK = 44;
    const REGION_ID_OREL = 45;

    const REGION_ID_SOCHI = 76;

    public static $regionSettings = [
        self::REGION_ID_KRASNOIARSK => ['date_start' => '2019-03-01', 'prefixes' => ['7391']],
        self::REGION_ID_SOCHI => ['date_start' => '2019-03-01'],
        self::REGION_ID_HABAROVSK => ['date_start' => '2018-01-01', 'prefixes' => ['74212']],
        self::REGION_ID_IZHEVSK => ['date_start' => '2019-03-26', 'prefixes' => ['7341']],
        self::REGION_ID_TOMSK => ['date_start' => '2019-03-27', 'prefixes' => ['7382']],
        self::REGION_ID_SARANSK => ['date_start' => '2019-04-01'],
        self::REGION_ID_MSK => ['date_start' => '2017-12-01', 'prefixes' => ['7495', '7499']],
        self::REGION_ID_CHBOKSARY => ['date_start' => '2019-05-28', 'prefixes' => ['7835']],
        self::REGION_ID_KAZAN => ['date_start' => '2019-07-01', 'prefixes' => ['7843']],
        self::REGION_ID_BARNAUL => ['date_start' => '2019-07-01', 'prefixes' => ['7385']],
        self::REGION_ID_EKATERINBURG => ['date_start' => '2016-11-01', 'prefixes' => ['7343']],
        self::REGION_ID_VLADIMIR => ['date_start' => '2019-08-28', 'prefixes' => ['7492']],
        self::REGION_ID_KOSTROMA => ['date_start' => '2019-09-18', 'prefixes' => ['7494']],
        self::REGION_ID_NOVOSIBIRSK => ['date_start' => '2019-09-30', 'prefixes' => ['7383']],
        self::REGION_ID_VOLGOGRAD => ['date_start' => '2019-10-31', 'prefixes' => ['7844']], //2017-02-01 - real
        self::REGION_ID_ULIANOVSK => ['date_start' => '2017-12-01', 'prefixes' => ['7842']],
        self::REGION_ID_BLAGOVESHCHENSK => ['date_start' => '2019-03-01', 'prefixes' => ['7416']],
        self::REGION_ID_YAKUTSK => ['date_start' => '2019-03-01', 'prefixes' => ['7411']],
        self::REGION_ID_VLADIVOSTOK => ['date_start' => '2019-03-01', 'prefixes' => ['7423']],
        self::REGION_ID_CHELABINSK => ['date_start' => '2019-03-01', 'prefixes' => ['7351']],
        self::REGION_ID_SARATOV => ['date_start' => '2019-05-16', 'prefixes' => ['7845']],
        self::REGION_ID_PSKOV => ['date_start' => '2019-03-01', 'prefixes' => ['7811']],
        self::REGION_ID_ULANUDE => ['date_start' => '2019-03-01', 'prefixes' => ['7301']],
        self::REGION_ID_SPB => ['date_start' => '2019-03-01', 'prefixes' => ['7812']],
        self::REGION_ID_KIROV => ['date_start' => '2018-06-01', 'prefixes' => ['7833']],
        self::REGION_ID_KURSK => ['date_start' => '2019-03-01', 'prefixes' => ['7471']],
        self::REGION_ID_STAVROPOL => ['date_start' => '2018-03-09', 'prefixes' => ['7865']],
        self::REGION_ID_KALININGRAD => ['date_start' => '2018-03-09', 'prefixes' => ['7401']],
        self::REGION_ID_VORONEJ => ['date_start' => '2019-03-01', 'prefixes' => ['7473']],
        self::REGION_ID_YAROSLAVL => ['date_start' => '2019-03-01', 'prefixes' => ['7485']],
        self::REGION_ID_TAMBOV => ['date_start' => '2019-03-01', 'prefixes' => ['7475']],
        self::REGION_ID_ARHANGELSK => ['date_start' => '2019-03-01', 'prefixes' => ['7818']],
        self::REGION_ID_KEMEROVO => ['date_start' => '2019-03-01', 'prefixes' => ['7384']],
        self::REGION_ID_PENZA => ['date_start' => '2019-03-01', 'prefixes' => ['7841']],
        self::REGION_ID_VOLOGDA => ['date_start' => '2019-07-01', 'prefixes' => ['7817']],
        self::REGION_ID_PETROZAVODSK => ['date_start' => '2019-07-01', 'prefixes' => ['7814']],
        self::REGION_ID_KALUGA => ['date_start' => '2019-07-01', 'prefixes' => ['7484']],
        self::REGION_ID_OMSK => ['date_start' => '2019-03-01', 'prefixes' => ['7381']],
        self::REGION_ID_TUMEN => ['date_start' => '2019-03-01', 'prefixes' => ['7345']],
        self::REGION_ID_BELGOROD => ['date_start' => '2019-03-01', 'prefixes' => ['7472']],
        self::REGION_ID_LIPETSK => ['date_start' => '2019-03-01', 'prefixes' => ['7474']],
        self::REGION_ID_SURGUT => ['date_start' => '2019-03-01', 'prefixes' => ['7346']],
        self::REGION_ID_BRANSK => ['date_start' => '2019-03-01', 'prefixes' => ['7483']],
        self::REGION_ID_IRKUTSK => ['date_start' => '2019-03-01', 'prefixes' => ['7395']],
        self::REGION_ID_SYKTYVKAR => ['date_start' => '2019-03-01', 'prefixes' => ['7821']],
        self::REGION_ID_KRASNODAR => ['date_start' => '2019-03-01', 'prefixes' => ['7861']],
        self::REGION_ID_NN => ['date_start' => '2019-03-01', 'prefixes' => ['7831']],
        self::REGION_ID_SMOLENSK => ['date_start' => '2021-06-03', 'prefixes' => ['7481']],
        self::REGION_ID_OREL => ['date_start' => '2019-03-01', 'prefixes' => ['7486']],
    ];

    public function attributeLabels()
    {
        return parent::attributeLabels() + [
                'region_id' => 'Регион',
                'name_full' => 'Имя',
                'inn' => 'ИНН',
                'bank' => 'Банк',
                'contact_fio' => 'Контактное ФИО',
                'contact_phone' => 'Контактный номер',
                'address_jur' => 'Юр. адресс / прописка',
                'address_post' => 'Почт. адресс',
                'contract_no' => 'Договор №',
                'legal_type' => 'Юр.тип',
                'account_manager' => 'Ак. менеджер',
                'filter_by' => 'Фильтр клиентов',
                'is_with_error' => 'Клиенты с ошибками',
                'is_active' => 'Сейчас активен',
            ] + (new ClientContragentPerson())->attributeLabels();
    }

    public function rules()
    {
        return [
            ['region_id', 'integer'],
            [['name_full', 'account_manager', 'filter_by', 'is_with_error', 'is_active'], 'string'],
        ];
    }

    /**
     * Основной фильтр
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = ClientAccount::find()
            ->alias('c')
            ->select('c.*');

        $query->where(['c.id' => $this->region_id ? $this->_getClientIds($this->region_id) : false]);
        $query->joinWith('clientContractModel AS cc');
        $query->joinWith('clientContractModel.clientContragent AS cg');
        $query->joinWith('clientContractModel.clientContragent.personModel AS cgp');

        $query->andWhere(['NOT', ['cc.business_process_status_id' => [22, 28, 19, 29]]]); // отказ, Мусор, Заказ услуг, Дубликат

        if ($this->account_manager) {
            $query->andWhere(['cc.account_manager' => $this->account_manager]);
        }

        $sqlUpload = 'cc.state != \'unchecked\' AND c.voip_credit_limit_day > 0 AND c.id NOT IN (44725, 51147, 54112, 52552, 52921, 46247)';

        switch ($this->filter_by) {
            case  self::FILTER_UPLOADED:
                $query->andWhere($sqlUpload);
                break;

            case self::FILTER_NOT_UPLOADED:
                $query->andWhere(['NOT', $sqlUpload]);
                break;

            case self::FILTER_ACTIVE:
                $query->andWhere(['c.is_active' => 1]);

            default:
                break;
        }

        if ($this->is_with_error) {
            $query1 = ClientContact::find();
            $query1->where(self::getContactWhere())
                ->andWhere('client_id = c.id')
                ->orderBy(self::getContactOrderBy())
                ->limit(1);

            $query2 = clone $query1;

            $query1->select('data');
            $query2->select('comment');

            $where = ['or',
                ['AND', ['NOT', ['cg.legal_type' => ClientContragent::PERSON_TYPE]], // для юр. лиц
                    ['OR',
                        ['c.bik' => null], ['c.bik' => ''],
                        ['c.bank_name' => null], ['c.bank_name' => ''],
                        ['c.pay_acc' => null], ['c.pay_acc' => ''],
                        ['cg.inn' => null], ['cg.inn' => ''],
                        ['cg.address_jur' => null], ['cg.address_jur' => ''],
                        ['c.address_post' => null], ['c.address_post' => ''],
                    ]
                ],

                ['AND', ['cg.legal_type' => ClientContragent::PERSON_TYPE], // для физ лиц
                    ['OR',
                        ['cgp.id' => null],
                        ['cgp.last_name' => null], ['cgp.last_name' => ''],
                        ['cgp.first_name' => null], ['cgp.first_name' => ''],
                        ['cgp.middle_name' => null], ['cgp.middle_name' => ''],
                        ['cgp.passport_date_issued' => null], ['cgp.passport_date_issued' => '1970-01-01'],
                        (new Expression('length(trim(coalesce(cgp.passport_serial, \'\'))) != 4')),
                        (new Expression('length(trim(coalesce(cgp.passport_number, \'\'))) != 6')),
                        ['cgp.passport_issued' => null], ['cgp.passport_issued' => ''],
                        ['cgp.registration_address' => null], ['cgp.registration_address' => ''],
                        ['cgp.birthday' => null], ['cgp.birthday' => '0000-00-00'],
                    ]
                ],
                'trim(coalesce((' . $query1->createCommand()->rawSql . '),\'\')) = \'\'',
                'trim(coalesce((' . $query2->createCommand()->rawSql . '),\'\')) = \'\'',
            ];

            if ($this->is_with_error == self::ERR_ALL) {
                $where[] = '(select count(*) from '.EquipmentUser::tableName().' eq where eq.client_account_id = c.id) = 0';
            }

            $query->andWhere($where);

        }

        $query->orderBy(['c.id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
//            'sort' => [
//                'defaultOrder' => [
//                    'cid' => SORT_DESC,
//                ]
//            ],
        ]);

        return $dataProvider;
    }

    /**
     * Параметры выборки для фильтра моделей контактов
     *
     * @return array
     */
    public static function getContactWhere()
    {
        return ['AND',
            ['type' => 'phone'],
            ['NOT', ['DATA' => 'test']],
            ['NOT', ['COMMENT' => 'autoconvert']],
            ['NOT', ['COMMENT' => '']],
            ['NOT', ['data' => '']],
            ['NOT', ['user_id' => 177]],
            ['IS NOT', 'comment', null],
            ['IS NOT', 'data', null],
        ];
    }

    /**
     * Параметры сортировки для фильра моделей контактов
     *
     * @return string
     */
    public static function getContactOrderBy()
    {
        return 'LENGTH(comment) desc, id DESC';
    }

    /**
     * Фильтр моделей контактов
     *
     * @param ClientAccount $account
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getContactByAccount(ClientAccount $account)
    {
        static $cache = [];

        if (isset($cache[$account->id])) {
            return $cache[$account->id];
        }


        $cache[$account->id] = $account->getContacts()
            ->where(self::getContactWhere())
            ->orderBy(self::getContactOrderBy())
            ->one();

        return $cache[$account->id];
    }

    /**
     * Список id ЛС
     *
     * @param int $regionId
     * @return array
     */
    private function _getClientIds($regionId)
    {
        $sqlPhoneList = self::getSqlPhoneList($regionId);
        $sql = <<<SQL
SELECT DISTINCT client_id
FROM (
       {$sqlPhoneList}
     ) a
ORDER BY client_id
SQL;
        return ClientAccount::getDb()->createCommand($sql)->queryColumn();
    }

    /**
     * Список id-услуг телефонии
     *
     * @param int $regionId
     * @return array
     */
    public static function getAccountTariffIds($regionId, $isEmptyDevice = null)
    {
        $sqlPhoneList = self::getSqlPhoneList($regionId);

        $addSql = '';

        if ($isEmptyDevice !== null) {
            $addSql = 'where ' . ($isEmptyDevice ? 'NOT ' : '') . 'device_address = \'\'';
        }

        return \Yii::$app->db->createCommand("SELECT u_id FROM ({$sqlPhoneList}) a " . $addSql)->queryColumn();
    }

    /**
     * Подготавливаем SQL для выдачи списка выгружаемых услуги для региона
     *
     * @param int $regionId
     * @return string
     * @throws \Exception
     */
    public static function getSqlPhoneList($regionId)
    {
        $regionSettings = isset(self::$regionSettings[$regionId]) ? self::$regionSettings[$regionId] : false;
        $dateStart = $regionSettings ? $regionSettings['date_start'] : (new \DateTime('now'))->modify('first day of this month')->modify('-3 year')->format(DateTimeZoneHelper::DATE_FORMAT);

        return <<<SQL
            SELECT 
                s.usage_id,
                s.client_id,
                e164,
                {$regionId} as region,
                if(actual_from < '{$dateStart}', '{$dateStart}', actual_from) as actual_from,
                actual_to,
                if(activation_dt < '{$dateStart} 00:00:00', '{$dateStart} 00:00:00', activation_dt) as activation_dt,
                IF(expire_dt >= '3000-01-01 00:00:00', NULL, expire_dt)                        AS expire_dt,
                trim(device_address) AS                                                       device_address
            FROM state_service_voip s
            WHERE
                s.region={$regionId} AND lines_amount > 0
                AND (actual_to IS NULL OR expire_dt > '{$dateStart} 00:00:00')
SQL;


    }
}
