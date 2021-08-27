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

    public static $regionSettings = [
        Region::KRASNOIARSK => ['date_start' => '2019-03-01'],
        Region::HABAROBSK => ['date_start' => '2018-01-01'],
        Region::NNOVGOROD => ['date_start' => '2018-01-01'],
        Region::MOSCOW => ['date_start' => '2017-01-01'],
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
  u_id,
  client_id,
  e164,
  a.region,
  if(actual_from < '{$dateStart} 00:00:00', '{$dateStart} 00:00:00', actual_from) actual_from,
  actual_to,
  device_address
FROM (
       SELECT a.* /*,
                (SELECT contract_no
                 FROM client_document cd, clients c
                 WHERE
                   c.id = a.client_id
                   AND cd.contract_id = c.contract_id
                   AND type = 'contract'
                 ORDER BY
                   cd.is_active DESC,
                   cd.contract_date DESC,
                   cd.id DESC
                 LIMIT 1) AS contract_no
                 */

       FROM (SELECT
               u.id                                                               as u_id,
               c.id                                                               AS client_id,
               u.e164,
               v.region,
               CONCAT(actual_from, ' 00:00:00')                                   AS actual_from,
               IF(actual_to > '3000-01-01', NULL, concat(actual_to, ' 23:59:59')) AS actual_to,
               u.address                                                          AS device_address
             FROM usage_voip u, voip_numbers v, clients c
             WHERE
               c.client = u.client
               AND v.region = {$regionId}
                                AND u.e164 = v.number
                                              AND actual_to >= '{$dateStart}'

                                              UNION

                                              SELECT *
                                              FROM (
       select u_id, client_id, e164, region, CONCAT( IF (actual_from < '2019-03-01', '2019-03-01', actual_from), ' 00:00:00') AS actual_from, actual_to, device_address from (
       SELECT u.id as u_id,
       client_account_id AS client_id,
       voip_number AS e164,
       v.region,
       ( SELECT actual_from_utc + INTERVAL 3 HOUR
         FROM uu_account_tariff_log
         WHERE account_tariff_id = u.id AND tariff_period_id IS NOT NULL
                                 ORDER BY actual_from_utc
                                       LIMIT 1)            actual_from,
       ( SELECT actual_from_utc + INTERVAL 3 HOUR
         FROM uu_account_tariff_log
         WHERE account_tariff_id = u.id AND tariff_period_id IS NULL
                                 ORDER BY actual_from_utc DESC
                                          LIMIT 1)            actual_to,
                                                              u.device_address
                                                              FROM uu_account_tariff u, voip_numbers v
                                                                                                     WHERE v.region = {$regionId} AND u.voip_number = v.number AND service_type_id = 2
                                                                                                                                                                             ) a
                                                   ) a
             WHERE actual_to IS NULL OR actual_to >= '{$dateStart} 00:00:00') a
       #HAVING contract_no IS NOT NULL
     ) a, clients c, client_contract cc
WHERE client_id NOT IN (44725, 51147, 54112, 52552, 52921, 46247)
and a.client_id = c.id and c.contract_id = cc.id
and cc.business_process_status_id not in (22, 28, 19, 29) ## отказ, Мусор, Заказ услуг, Дубликат

SQL;


    }
}
