<?php

namespace app\modules\uu\filter;

use app\classes\Assert;
use app\classes\Html;
use app\classes\traits\GetListTrait;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\Number;
use app\models\User;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffHeap;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffLogAdd;
use app\modules\uu\models\AccountTrouble;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\models\ClientContragent;
use app\models\ClientContract;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Фильтрация для AccountTariff
 */
class AccountTariffFilter extends AccountTariff
{
    public $id = '';
    public $client_account_id = '';
    public $prev_account_tariff_tariff_id = '';
    public $region_id = '';
    public $city_id = '';
    public $is_active = '';
    public $is_active_client_account = '';

    public $service_type_id = '';
    public $tariff_period_id = '';
    public $beauty_level = '';

    public $infrastructure_project = '';
    public $infrastructure_level = '';
    public $datacenter_id = '';
    public $price_from = '';
    public $price_to = '';

    public $tariff_status_id = '';
    public $tariff_is_include_vat = '';
    public $tariff_country_id = '';
    public $tariff_currency_id = '';
    public $tariff_organization_id = '';
    public $client_organization_id = '';
    public $tariff_is_default = '';

    public $number_ndc_type_id = '';

    public $tariff_period_utc_from = '';
    public $tariff_period_utc_to = '';

    public $account_log_period_utc_from = '';
    public $account_log_period_utc_to = '';

    public $is_unzipped = '';

    public $account_manager_name = '';
    public $account_manager = '';

    // Проксируемые фильтруемые значения
    public $date_sale_from = '';
    public $date_sale_to = '';
    public $date_before_sale_from = '';
    public $date_before_sale_to = '';

    public $test_connect_date_to = '';
    public $test_connect_date_from = '';
    public $disconnect_date_to = '';
    public $disconnect_date_from = '';

    // Связанный лид с услугами
    public $trouble_id = '';

    public $is_device_empty = '';

    public $price_level = '';
    
    // Поиск по контрагенту
    public $contragent_type = '';

    /**
     * @param int $serviceTypeId
     */
    public function __construct($serviceTypeId = 0)
    {
        $this->service_type_id = $serviceTypeId;
        parent::__construct();
    }

    /**
     * @return ServiceType
     */
    public function getServiceType()
    {
        return $this->service_type_id ?
            ServiceType::findOne($this->service_type_id) :
            null;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'account_manager' => 'Ак. Менеджер',
            'is_device_empty' => 'Адрес устройства заполнен',
            'is_active_client_account' => 'Статус клиента',
        ]);
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['beauty_level', 'prev_account_tariff_tariff_id'], 'integer'];
        $rules[] = [['price_from', 'price_to'], 'integer'];
        $rules[] = [
            [
                'tariff_period_id',
                'tariff_status_id',
                'tariff_is_include_vat',
                'tariff_country_id',
                'tariff_organization_id',
                'client_organization_id',
                'tariff_is_default',
                'trouble_id',
                'contragent_type',
            ],
            'integer'
        ];
        $rules[] = [['tariff_currency_id'], 'string'];
        $rules[] = [['number_ndc_type_id', 'price_level'], 'integer'];
        $rules[] = [['tariff_period_utc_from', 'tariff_period_utc_to'], 'string'];
        $rules[] = [['account_log_period_utc_from', 'account_log_period_utc_to'], 'string'];
        $rules[] = [['is_unzipped', 'is_device_empty', 'is_active_client_account'], 'integer'];
        $rules[] = [
            [
                'account_manager_name',
                'test_connect_date_to',
                'test_connect_date_from',
                'disconnect_date_to',
                'disconnect_date_from',
                'date_sale_from',
                'date_sale_to',
                'date_before_sale_from',
                'date_before_sale_to',
                'account_manager',
            ],
            'string'
        ];
        return $rules;
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffHeap = AccountTariffHeap::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $accountTroubleTableName = AccountTrouble::tableName();
        $tariffTableName = Tariff::tableName();
        $numberTableName = Number::tableName();

        $query = AccountTariff::find()
            ->joinWith('clientAccount')
            ->joinWith('region')
            ->joinWith('tariffPeriod')

            ->with('serviceType')
            ->with('prevAccountTariff.tariffPeriod.chargePeriod')
            ->with('prevAccountTariff.tariffPeriod.tariff.currency')
            ->with('tariffPeriod.chargePeriod')
            ->with('tariffPeriod.tariff.currency')
            ->with('tariffPeriod.tariff.tariffCountries.country')
            ->with('tariffPeriod.tariff.organizations.organization')

            ->with('accountTariffLogs.tariffPeriod.tariff.currency')
            ->with('accountTariffLogs.tariffPeriod.chargePeriod')
            ->with('number')
            ->with('number.imsiModel')

            ->leftJoin("{$accountTroubleTableName} at", "{$accountTariffTableName}.id = at.account_tariff_id")
            ->leftJoin("{$accountTariffHeap} uath", "uath.account_tariff_id = {$accountTariffTableName}.id")
            ->leftJoin("{$tariffTableName} tariff", 'tariff.id = ' . $tariffPeriodTableName . '.tariff_id');

        if ($this->serviceType && $this->serviceType->isPackage()) {
            $query
                ->leftJoin($accountTariffTableName . ' account_tariff_prev', "account_tariff_prev.id = {$accountTariffTableName}.prev_account_tariff_id")
                ->leftJoin($tariffPeriodTableName . ' tariff_period_prev', 'account_tariff_prev.tariff_period_id = tariff_period_prev.id');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(["{$accountTariffTableName}.id" => $this->id]);
        $this->client_account_id !== '' && $query->andWhere([$accountTariffTableName . '.client_account_id' => $this->client_account_id]);
        $this->region_id !== '' && $query->andWhere([$accountTariffTableName . '.region_id' => $this->region_id]);
        $this->city_id !== '' && $query->andWhere([$accountTariffTableName . '.city_id' => $this->city_id]);
        // ???
        $this->is_active !== '' && $query->andWhere([$accountTariffTableName . '.is_active' => $this->is_active]);

        $this->is_active_client_account !== '' && $query->andWhere([ClientAccount::tableName().'.is_active' => 1]);

        $this->is_unzipped !== '' && $query->andWhere([$accountTariffTableName . '.is_unzipped' => $this->is_unzipped]);

        $this->tariff_status_id !== '' && $query->andWhere(['tariff.tariff_status_id' => $this->tariff_status_id]);
        $this->tariff_is_include_vat !== '' && $query->andWhere(['tariff.is_include_vat' => $this->tariff_is_include_vat]);
        $this->tariff_currency_id !== '' && $query->andWhere(['tariff.currency_id' => $this->tariff_currency_id]);
        $this->tariff_is_default !== '' && $query->andWhere(['tariff.is_default' => $this->tariff_is_default]);

        if ($this->tariff_country_id !== '') {
            $query
                ->innerJoin(TariffCountry::tableName() . ' as tariff_country', 'tariff.id = tariff_country.tariff_id')
                ->andWhere(['tariff_country.country_id' => $this->tariff_country_id]);
        }

        $isEmptyAccountManagerName = ($this->account_manager_name !== '');
        $isSpecialServiceType = in_array($this->service_type_id, [
            ServiceType::ID_VPBX,
            ServiceType::ID_VOIP,
            ServiceType::ID_CALL_CHAT
        ]);

        $isClientContractJoined = false;
        $clientContractTableName = ClientContract::tableName();

        if ($this->account_manager_name !== '' || $isSpecialServiceType) {
            $query->innerJoin($clientContractTableName, "clients.contract_id = $clientContractTableName.id");
            $isClientContractJoined = true;

            // Присоединение столбца "Ак. менеджер"
            if ($isEmptyAccountManagerName) {
                $query
                    ->leftJoin(User::tableName() . ' amu', $clientContractTableName . '.account_manager = amu.user')
                    ->andWhere(['amu.user' => $this->account_manager_name]);
            } elseif ($this->account_manager) {
                $query->andWhere([$clientContractTableName . '.account_manager' => $this->account_manager]);
            }


            if ($isSpecialServiceType) {

                if ($this->service_type_id == ServiceType::ID_VOIP) {
                    // Фильтрация столбца "Дата включения на тестовый тариф"
                    $this->test_connect_date_from !== '' && $query->andWhere(['>=', 'uath.test_connect_date', $this->test_connect_date_from . ' 00:00:00']);
                    $this->test_connect_date_to !== '' && $query->andWhere(['<=', 'uath.test_connect_date', $this->test_connect_date_to . ' 23:59:59']);
                }

                // Фильтрация столбца "Дата продажи"
                $this->date_sale_from !== '' && $query->andWhere(['>=', 'uath.date_sale', $this->date_sale_from . ' 00:00:00']);
                $this->date_sale_to !== '' && $query->andWhere(['<=', 'uath.date_sale', $this->date_sale_to . ' 23:59:59']);

                // Фильтрация столбца "Дата допродажи"
                $this->date_before_sale_from !== '' && $query->andWhere(['>=', 'uath.date_before_sale', $this->date_before_sale_from . ' 00:00:00']);
                $this->date_before_sale_to !== '' && $query->andWhere(['<=', 'uath.date_before_sale', $this->date_before_sale_to . ' 23:59:59']);

                // Фильтрация столбца "Дата отключения"
                $this->disconnect_date_from !== '' && $query->andWhere(['>=', 'uath.disconnect_date', $this->disconnect_date_from . ' 00:00:00']);
                $this->disconnect_date_to !== '' && $query->andWhere(['<=', 'uath.disconnect_date', $this->disconnect_date_to . ' 23:59:59']);
            }
        }

        if ($this->tariff_organization_id !== '') {
            $query->leftJoin(
                TariffOrganization::tableName() . ' tariff_organization',
                'tariff_organization.tariff_id = tariff.id'
            )
                ->andWhere(['tariff_organization.organization_id' => $this->tariff_organization_id]);
        }

        if ($this->client_organization_id !== '') {
            if (!$isClientContractJoined) {
                $query->innerJoin($clientContractTableName, "clients.contract_id = $clientContractTableName.id");
                $isClientContractJoined = true;
            }

            $query->andWhere([$clientContractTableName.'.organization_id' => $this->client_organization_id]);
        }

        if ($this->contragent_type) {
            $clientContragentTableName = ClientContragent::tableName();
            
            if (!$isClientContractJoined) {
                $clientContractTableName = ClientContract::tableName();
                $query->innerJoin($clientContractTableName, "clients.contract_id = {$clientContractTableName}.id");
                $isClientContractJoined = true;
            }
            
            $query
                ->innerJoin($clientContragentTableName, "{$clientContractTableName}.contragent_id = {$clientContragentTableName}.id")
                ->andWhere([$clientContragentTableName . '.legal_type' => $this->contragent_type]);
        }

        if ($this->service_type_id == ServiceType::ID_VOIP) {
            $query
                ->joinWith('number')
                ->with('number');
            $this->number_ndc_type_id !== '' && $query->andWhere([$numberTableName . '.ndc_type_id' => $this->number_ndc_type_id]);
        }

        if ($this->service_type_id == ServiceType::ID_VOIP_PACKAGE_CALLS && $this->number_ndc_type_id) {
            $query
            ->leftJoin(Number::tableName() . ' prevNm',  'account_tariff_prev.voip_number = prevNm.number')
            ->andWhere(['prevNm.ndc_type_id' => $this->number_ndc_type_id]);
        }

        switch ($this->prev_account_tariff_tariff_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere(['account_tariff_prev.tariff_period_id' => null]);
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere('account_tariff_prev.tariff_period_id IS NOT NULL');
                break;
            default:
                $query->andWhere(['tariff_period_prev.id' => $this->prev_account_tariff_tariff_id]);
                break;
        }

        $this->voip_number = strtr($this->voip_number, ['.' => '_', '*' => '%']);
        $this->voip_number && $query->andWhere(['LIKE', 'voip_number', $this->voip_number, $isEscape = false]);

        $this->service_type_id && $query->andWhere([$accountTariffTableName . '.service_type_id' => $this->service_type_id]);

        $this->infrastructure_project && $query->andWhere([$accountTariffTableName . '.infrastructure_project' => $this->infrastructure_project]);
        $this->infrastructure_level && $query->andWhere([$accountTariffTableName . '.infrastructure_level' => $this->infrastructure_level]);
        $this->datacenter_id && $query->andWhere([$accountTariffTableName . '.datacenter_id' => $this->datacenter_id]);

        $this->price_from !== '' && $query->andWhere(['>=', $accountTariffTableName . '.price', $this->price_from]);
        $this->price_to !== '' && $query->andWhere(['<=', $accountTariffTableName . '.price', $this->price_to]);

        $this->tariff_period_utc_from !== '' && $query->andWhere(['>=', $accountTariffTableName . '.tariff_period_utc', $this->tariff_period_utc_from]);
        $this->tariff_period_utc_to !== '' && $query->andWhere(['<=', $accountTariffTableName . '.tariff_period_utc', $this->tariff_period_utc_to]);

        $this->account_log_period_utc_from !== '' && $query->andWhere(['>=', $accountTariffTableName . '.account_log_period_utc', $this->account_log_period_utc_from]);
        $this->account_log_period_utc_to !== '' && $query->andWhere(['<=', $accountTariffTableName . '.account_log_period_utc', $this->account_log_period_utc_to]);

        $this->beauty_level !== '' && $query->andWhere([$numberTableName . '.beauty_level' => $this->beauty_level]);
        $this->price_level !== '' && $query->andWhere([ClientAccount::tableName() . '.price_level' => $this->price_level]);

        $this->trouble_id !== '' && $query->andWhere(['at.trouble_id' => $this->trouble_id]);

        $qWhere = ['at.device_address' => ''];

        switch ($this->is_device_empty) {
            case '';
                break;
            case TariffPeriod::IS_NOT_SET:
                $query->andWhere(['NOT', $qWhere]);
                break;

            case TariffPeriod::IS_SET:
                $query->andWhere($qWhere);
                break;
        }

        switch ($this->tariff_period_id) {
            case '':
                break;
            case TariffPeriod::IS_NOT_SET:
                $query->andWhere([$accountTariffTableName . '.tariff_period_id' => null]);
                break;
            case TariffPeriod::IS_SET:
                $query->andWhere($accountTariffTableName . '.tariff_period_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$accountTariffTableName . '.tariff_period_id' => $this->tariff_period_id]);
                break;
        }

        return $dataProvider;
    }

    /**
     * Получить запрос для списка
     *
     * @param int $id
     * @param int $serviceTypeId
     * @param int $clientAccountId
     * @param int $regionId
     * @param int $cityId
     * @param int $voipNumber
     * @param int $prevAccountTariffId
     * @param int $limit
     * @param int $offset
     * @return \yii\db\ActiveQuery
     */
    public static function getListQuery($id, $serviceTypeId, $clientAccountId, $regionId, $cityId, $voipNumber, $prevAccountTariffId, $limit, $offset)
    {
        $query = AccountTariff::find();

        $id && $query->andWhere(['id' => (int)$id]);
        $serviceTypeId && $query->andWhere(['service_type_id' => (int)$serviceTypeId]);
        $clientAccountId && $query->andWhere(['client_account_id' => (int)$clientAccountId]);
        $regionId && $query->andWhere(['region_id' => (int)$regionId]);
        $cityId && $query->andWhere(['city_id' => (int)$cityId]);
        $voipNumber && $query->andWhere(['voip_number' => $voipNumber]);
        $prevAccountTariffId && $query->andWhere(['prev_account_tariff_id' => $prevAccountTariffId]);

        // deprecated
        $query->limit($limit);

        $offset && $query->offset($offset);
        $query->orderBy(['id' => SORT_DESC]);

        $query
            ->with('number')
            ->with('serviceType.resources')
            ->with('region')
            ->with('city')
            ->with('clientAccount')
            ->with('accountTariffLogs.accountTariff.clientAccount')
            ->with('accountLogPeriods')
            ->with('accountTariffLogs.tariffPeriod.chargePeriod')
            ->with('accountTariffLogs.tariffPeriod.tariff.tariffVoipCountries.country')
            ->with('accountTariffLogs.tariffPeriod.tariff.package')
            ->with('accountTariffLogs.tariffPeriod.tariff.serviceType')
            ->with('accountTariffLogs.tariffPeriod.tariff.status')
            ->with('accountTariffLogs.tariffPeriod.tariff.person')
            ->with('accountTariffLogs.tariffPeriod.tariff.tag')
            ->with('accountTariffLogs.tariffPeriod.tariff.tariffResources.resource.serviceType')
            ->with('accountTariffLogs.tariffPeriod.tariff.voipGroup')
            ->with('accountTariffLogs.tariffPeriod.tariff.voipCities.city')
            ->with('accountTariffLogs.tariffPeriod.tariff.voipNdcTypes.ndcType')
            ->with('accountTariffLogs.tariffPeriod.tariff.organizations.organization')
            ->with('accountTariffLogs.tariffPeriod.tariff.packageMinutes.destination')
            ->with('accountTariffLogs.tariffPeriod.tariff.packagePrices.destination')
            ->with('accountTariffLogs.tariffPeriod.tariff.packagePricelists.pricelist')
            ->with('accountTariffResourceLogsAll.accountTariff.clientAccount')
            ->with('nextAccountTariffsEager')
            ->with('accountLogPeriodLast')
            ->with('accountLogPeriodLast.minutesSummary')
        ;

        return $query;
    }

    /**
     * Получить запрос для списка с пакетами
     *
     * @param int $id
     * @param int $clientAccountId
     * @param int $serviceTypeId
     * @param int $voipNumber
     * @param int $limit
     * @param int $offset
     * @return \yii\db\ActiveQuery
     */
    public static function getListWithPackagesQuery($id, $clientAccountId, $serviceTypeId, $voipNumber, $limit, $offset)
    {
        $query = AccountTariff::find();

        $query
            ->with('number')
            ->with('serviceType.resources')
            ->with('region')
            ->with('city')
            ->with('clientAccount')

            ->with('tariffPeriod.tariff.tariffResourcesIndexedByResourceId')
            ->with('accountTariffLogs.accountTariff.clientAccount')
            ->with('accountLogPeriods')

            ->with('accountTariffLogs.tariffPeriod.chargePeriod')
            ->with('accountTariffLogs.tariffPeriod.tariff.tariffVoipCountries.country')
            ->with('accountTariffLogs.tariffPeriod.tariff.package')
            ->with('accountTariffLogs.tariffPeriod.tariff.serviceType')
            ->with('accountTariffLogs.tariffPeriod.tariff.status')
            ->with('accountTariffLogs.tariffPeriod.tariff.person')
            ->with('accountTariffLogs.tariffPeriod.tariff.tag')
            ->with('accountTariffLogs.tariffPeriod.tariff.tariffResources.resource.serviceType')
            ->with('accountTariffLogs.tariffPeriod.tariff.voipGroup')
            ->with('accountTariffLogs.tariffPeriod.tariff.voipCities.city')
            ->with('accountTariffLogs.tariffPeriod.tariff.voipNdcTypes.ndcType')
            ->with('accountTariffLogs.tariffPeriod.tariff.organizations.organization')
            ->with('accountTariffLogs.tariffPeriod.tariff.packageMinutes.destination')
            ->with('accountTariffLogs.tariffPeriod.tariff.packagePrices.destination')
            ->with('accountTariffLogs.tariffPeriod.tariff.packagePricelists.pricelist')

            ->with('accountTariffResourceLogsAll.accountTariff.clientAccount')

            ->with('accountLogPeriodLast')
            ->with('accountLogPeriodLast.minutesSummary')

            ->with('nextAccountTariffsEager');

        $id && $query->andWhere(['id' => (int)$id]);
        $clientAccountId && $query->andWhere(['client_account_id' => (int)$clientAccountId]);
        $serviceTypeId && $query->andWhere(['service_type_id' => (int)$serviceTypeId]);
        $voipNumber && $query->andWhere(['voip_number' => $voipNumber]);

        $offset && $query->offset($offset);
        $query->orderBy(new Expression('IF (tariff_period_id IS NULL, 1, 0)'));
        $query->addOrderBy([
            'id' => SORT_DESC
        ]);

        // deprecated
        $query->limit($limit);

        return $query;
    }

    /**
     * Получить запрос для Excel
     *
     * @param int $clientAccountId
     * @param int $serviceTypeId
     * @return \yii\db\ActiveQuery
     */
    public static function getListForExcelQuery($clientAccountId, $serviceTypeId)
    {
        $query = AccountTariff::find()

            ->with('number')
            ->with('city')
            ->with('clientAccount')
            ->with('accountTariffLogs')
            ->with('accountTariffLogs.tariffPeriod.tariff')

            ->andWhere([
                'client_account_id' => $clientAccountId,
                'service_type_id' => $serviceTypeId,
            ]);

        return $query;
    }

    public function doTask(\app\models\Task $task, $post)
    {
        $tariffPeriodIdAdd = false;
        $serviceTypeId = null;
        if (isset($post['AddPackageButton']) && isset($post['AccountTariffLogAdd']['tariff_period_id'])) {
            $tariffPeriodIdAdd = $post['AccountTariffLogAdd']['tariff_period_id'];
            $tariffPeriodAdd = TariffPeriod::findOne(['id' => $tariffPeriodIdAdd]);
            Assert::isObject($tariffPeriodAdd);
            $serviceTypeId = $tariffPeriodAdd->tariff->service_type_id;
        }

        /** @var ActiveQuery $query */
        $query = $this->search()->query;
        /** @var AccountTariff $accountTariff */

        $count = 0;

        $task->setStatus('run');
        $task->setCountAll($query->count());
        $task->setCount($count);

        foreach ($query->each() as $accountTariff) {
            $task->setCount($count++);

            $transaction = \Yii::$app->db->beginTransaction();

            try {
                if (isset($post['AddPackageButton']) && isset($post['AccountTariffLogAdd']['tariff_period_id'])) { // add
                    Assert::isNotNull($serviceTypeId);
                    Assert::isGreater($serviceTypeId, 0);

                    if ($accountTariff->prev_account_tariff_id) {
                        $accountTariff = $accountTariff->prevAccountTariff;
                    }

                    $isAlreadyAdded = false;
                    foreach ($accountTariff->nextAccountTariffs as $package) {

                        if ($package->tariff_period_id == $tariffPeriodIdAdd) {
                            $isAlreadyAdded = true;
                            break;
                        }

                        // в будущем
                        $logs = $package->accountTariffLogs;
                        $log = reset($logs);
                        if ($log->tariff_period_id == $tariffPeriodIdAdd) {
                            $isAlreadyAdded = true;
                            break;
                        }
                    }

                    if (!$isAlreadyAdded) {
                        // подключить базовый пакет
                        $accountTariffPackage = new AccountTariff();
                        $accountTariffPackage->client_account_id = $accountTariff->client_account_id;
                        $accountTariffPackage->service_type_id = $serviceTypeId;
                        $accountTariffPackage->region_id = $accountTariff->region_id;
                        $accountTariffPackage->city_id = $accountTariff->city_id;
                        $accountTariffPackage->prev_account_tariff_id = $accountTariff->id;
                        if (!$accountTariffPackage->save()) {
                            throw new ModelValidationException($accountTariffPackage);
                        }

                        $accountTariffPackageLog = new AccountTariffLogAdd();
                        $accountTariffPackageLog->account_tariff_id = $accountTariffPackage->id;

                        if (!$accountTariffPackageLog->load($post)) {
                            throw new \LogicException('данные для добавления не получены');
                        }

                        if (!$accountTariffPackageLog->save()) {
                            throw new ModelValidationException($accountTariffPackageLog);
                        }

                        $task->log($count . ': success: К ' . $accountTariff->getLink() . ' подключен пакет-тариф: ' .
                            Html::a(Html::encode($accountTariffPackage->getName(false)), $accountTariffPackage->getUrl()));

//                        \Yii::$app->session->addFlash('success', 'К ' . $accountTariff->getLink() . ' подключен пакет-тариф: ' .
//                            Html::a(Html::encode($accountTariffPackage->getName(false)), $accountTariffPackage->getUrl()));
                    } else {
                        $task->log($count . ': success: Подключаемый тариф-пакет уже включен: ' . $accountTariff->getLink());
//                        \Yii::$app->session->addFlash('success', 'Подключаемый тариф-пакет уже включен: ' . $accountTariff->getLink());
                    }
                } else { // change && close

                    $accountTariffLog = new AccountTariffLog;
                    $accountTariffLog->account_tariff_id = $accountTariff->id;

                    $accountTariffLog->load($post);

                    // Отключение услуги
                    if (isset($post['closeTariff'])) {
                        $accountTariffLog->tariff_period_id = null;
                    }

                    if (!$accountTariffLog->validate() || !$accountTariffLog->save()) {
                        throw new ModelValidationException($accountTariffLog);
                    }
                    $task->log($count . ': success: ' . $accountTariff->getLink() . ' обновлена');
//                    \Yii::$app->session->addFlash('success', $accountTariff->getLink() . ' обновлена');
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                $task->log($count . '; error: ' . $accountTariff->getLink() . ":\n" . $e->getMessage());
//                \Yii::$app->session->addFlash('error', $accountTariff->getLink() . ":\n" . $e->getMessage());
            }
        }

        $task->setCount($count);
        $task->setStatus('done');

        return true;
    }
}
