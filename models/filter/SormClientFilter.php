<?php

namespace app\models\filter;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Region;
use app\models\User;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для SormClient
 */
class SormClientFilter extends ClientAccount
{
    public $region_id = '';
    public $account_manager = '';

    public static $regionSettings = [
        Region::KRASNOIARSK => ['date_start' => '2019-03-01'],
        Region::HABAROBSK => ['date_start' => '2018-01-01'],
        Region::NNOVGOROD => ['date_start' => '2018-01-01'],
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
                'address_jur' => 'Юр. адресс',
                'contract_no' => 'Договор №',
                'legal_type' => 'Юр.тип',
                'account_manager' => 'Ак. менеджер'
            ];
    }

    public function rules()
    {
        return [
            ['region_id', 'integer'],
            [['name_full', 'account_manager'], 'string'],
        ];
    }

    /**
     * Основной фильтр
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = ClientAccount::find();

        $query->where([ClientAccount::tableName() . '.id' => $this->region_id ? $this->_getClientIds($this->region_id) : false]);
        if ($this->account_manager) {
            $query->joinWith('clientContractModel');
            $query->andWhere([ClientContract::tableName() . '.account_manager' => $this->account_manager]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
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
        return $account->getContacts()
            ->where(self::getContactWhere())
            ->orderBy(self::getContactOrderBy())
            ->one();
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
    public static function getAccountTariffIds($regionId)
    {
        $sqlPhoneList = self::getSqlPhoneList($regionId);
        return \Yii::$app->db->createCommand("SELECT u_id FROM ({$sqlPhoneList}) a ")->queryColumn();
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
        $dateStart = $regionSettings ? $regionSettings['date_start'] : (new \DateTime('now'))->modify('first day of this month')->format(DateTimeZoneHelper::DATE_FORMAT);

        return <<<SQL
SELECT
         u_id,
         client_id,
         e164,
         region,
         if(actual_from < '{$dateStart} 00:00:00' , '{$dateStart} 00:00:00' , actual_from) actual_from,
         actual_to,
         device_address
       FROM (
              SELECT
                a.*/*,
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
                      u.id                                                                           as u_id,
                      c.id                                                                           AS client_id,
                      u.e164,
                      v.region,
                      CONCAT(actual_from, ' 00:00:00') AS actual_from,
                      IF(actual_to > '3000-01-01', NULL, concat(actual_to, ' 23:59:59'))             AS actual_to,
                      u.address                                                                      AS device_address
                    FROM usage_voip u, voip_numbers v, clients c
                    WHERE
                      c.client = u.client
                      AND v.region = {$regionId}
                                       AND u.e164 = v.number
                                                     AND actual_to >= '{$dateStart}'

                                                     UNION

                                                     SELECT *
                                                     FROM (
                                                     select u_id, client_id, e164, region, CONCAT(IF(actual_from < '2019-03-01', '2019-03-01', actual_from), ' 00:00:00') AS actual_from, actual_to, device_address from (
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
            ) a
       WHERE client_id NOT IN (44725, 51147, 54112, 52552, 52921, 46247)
SQL;


    }
}
