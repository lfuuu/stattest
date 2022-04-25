<?php

namespace app\modules\uu\forms;

use app\classes\Form;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\helpers\Semaphore;
use app\models\EventQueue;
use app\models\Region;
use app\models\usages\UsageInterface;
use app\models\UsageTrunk;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\AccountTariffVoip;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use InvalidArgumentException;
use LogicException;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Class AccountTariffForm
 */
abstract class AccountTariffForm extends Form
{
    /** @var int */
    public $serviceTypeId;

    /** @var AccountTariff */
    public $accountTariff;

    /** @var AccountTariffLog */
    public $accountTariffLog;

    /** @var AccountTariffVoip */
    public $accountTariffVoip = null;

    /** @var int */
    public $ndcTypeId = null;

    public $postData = null;

    /**
     * @return AccountTariff
     */
    abstract public function getAccountTariffModel();


    /**
     * Показывать ли предупреждение, что необходимо выбрать клиента
     *
     * @return bool
     */
    abstract public function getIsNeedToSelectClient();

    /**
     * Конструктор
     *
     * @throws \InvalidArgumentException
     */
    public function init()
    {
        $this->accountTariff = $this->getAccountTariffModel();

        if ($this->serviceTypeId === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter usage type'));
        }

        $this->accountTariffLog = new AccountTariffLog();
        $this->accountTariffLog->account_tariff_id = $this->accountTariff->id;
        $this->accountTariffLog->actual_from = $this->accountTariff->getDefaultActualFrom();
        $this->accountTariffLog->populateRelation('accountTariff', $this->accountTariff);

        $this->accountTariffVoip = new AccountTariffVoip();
        $this->accountTariffVoip->voip_country_id = $this->accountTariff->clientAccount->country_id;
        $this->accountTariffVoip->city_id = $this->accountTariff->city_id;
        $this->accountTariffVoip->voip_ndc_type_id = $this->ndcTypeId;

        // init region id for a2p
        if ($this->serviceTypeId == ServiceType::ID_A2P && Yii::$app->request->isGet) {
            $this->accountTariff->region_id = Region::MOSCOW;
        }

        /** @var array $post */
        $post = $this->postData ?: Yii::$app->request->post();
        if (!$post) {
            return;
        }

        // Обработать submit (создать, редактировать, удалить)
//        Semaphore::me()->acquire(Semaphore::ID_UU_CALCULATOR);
        $this->loadFromInput($post);
//        Semaphore::me()->release(Semaphore::ID_UU_CALCULATOR);
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    public function loadFromInput($post)
    {
        /** @var array $post */
        $transaction = \Yii::$app->db->beginTransaction();

        // при создании услуга + лог в одной форме, при редактировании - в разных
        $isNewRecord = $this->accountTariff->isNewRecord;

        try {

            // при создании услуги телефонии свой интерфейс, из которого данные надо преобразовать в нужный формат
            Yii::info('AccountTariffForm. Before accountTariffVoip', 'uu');
            if ($isNewRecord && $this->serviceTypeId == ServiceType::ID_VOIP
                && $this->accountTariffVoip->load($post)
                && $this->accountTariffLog->load($post)
            ) {

                if (!$this->accountTariffVoip->validate()) {
                    $this->validateErrors += $this->accountTariffVoip->getFirstErrors();
                    throw new InvalidArgumentException('');
                }

                $this->accountTariff->city_id = $this->accountTariffVoip->city_id;

                if (!$this->accountTariffVoip->voip_numbers) {
                    $this->validateErrors[] = 'Не выбраны телефонные номера';
                    throw new InvalidArgumentException('');
                }

                if ($this->accountTariffVoip->device_address) {
                    $this->accountTariff->device_address = $this->accountTariffVoip->device_address;
                }

                // каждый выбранный номер телефона - отдельная услуга
                foreach ($this->accountTariffVoip->voip_numbers as $voipNumber) {

                    // услуга
                    Yii::info('AccountTariffForm. Before voip $accountTariff->save', 'uu');
                    $accountTariff = clone $this->accountTariff;
                    $accountTariff->voip_number = $voipNumber;
                    $accountTariff->voip_numbers_warehouse_status = $this->accountTariffVoip->voip_numbers_warehouse_status;
                    $accountTariff->addParam('voip_numbers_warehouse_status', $accountTariff->voip_numbers_warehouse_status);
                    $accountTariff->id = 0;
                    unset($accountTariff->number); // populateRelation
                    if (!$accountTariff->save()) {
                        $this->validateErrors += $accountTariff->getFirstErrors();
                        throw new ModelValidationException($accountTariff);
                    }

                    // лог тарифов
                    Yii::info('AccountTariffForm. Before voip $accountTariffLog->save', 'uu');
                    $accountTariffLog = clone $this->accountTariffLog;
                    $accountTariffLog->account_tariff_id = $accountTariff->id;
                    $accountTariffLog->populateRelation('accountTariff', $accountTariff);
                    $accountTariffLog->id = 0;
                    if (!$accountTariffLog->save()) {
                        $this->validateErrors += $accountTariffLog->getFirstErrors();
                        throw new ModelValidationException($accountTariffLog);
                    }
                }

                $this->isSaved = true;

                $this->accountTariffLog->account_tariff_id = 0; // вернуть обратно. 0 - потому что $isNewRecord
                $this->accountTariffLog->populateRelation('accountTariff', null);
                $post = []; // чтобы дальше логика универсальных услуг не отрабатывала
            }

            // Создание/редактирование обычной услуги
            Yii::info('AccountTariffForm. Before accountTariff->load', 'uu');
            if ($this->accountTariff->load($post)) {

                if ($this->accountTariff->service_type_id == ServiceType::ID_VPBX
                    && $this->accountTariff->isAttributeChanged('region_id')
                ) {
                    EventQueue::go(\app\modules\uu\Module::EVENT_VPBX, [
                        'client_account_id' => $this->accountTariff->client_account_id,
                        'account_tariff_id' => $this->accountTariff->id,
                        'region_id' => $this->accountTariff->region_id,
                    ]);
                }

                if ($this->accountTariff->service_type_id == ServiceType::ID_A2P && isset($post['AccountTariff']['route_name'])) {
                    $this->accountTariff->route_name = $post['AccountTariff']['route_name'];
                }

                // услуга
                if ($this->accountTariff->save()) {
                    $this->id = $this->accountTariff->id;
                    $this->isSaved = true;

                    if ($this->accountTariff->service_type_id == ServiceType::ID_TRUNK && isset($post['trunkId']) && $this->accountTariffLog->load($post)) {
                        // isset($post['trunkId']) гарантирует, что сюда попадаем только при создании не-мультитранка, но не при редактировании (там return) и не при создании мультитранка (там disabled)
                        if (!(int)$post['trunkId']) {
                            throw new LogicException('Не указан транк');
                        }

                        // дополнительно создать "логический транк"
                        $usageTrunk = new UsageTrunk;
                        $usageTrunk->id = $this->accountTariff->id;
                        $usageTrunk->client_account_id = $this->accountTariff->client_account_id;
                        $usageTrunk->connection_point_id = $this->accountTariff->region_id;
                        $usageTrunk->trunk_id = (int)$post['trunkId'];
                        $usageTrunk->actual_from = $this->accountTariffLog->actual_from;
                        $usageTrunk->actual_to = UsageInterface::MAX_POSSIBLE_DATE;
                        $usageTrunk->status = UsageTrunk::STATUS_CONNECTING;
                        if (!$usageTrunk->save()) {
                            throw new ModelValidationException($usageTrunk);
                        }
                    }
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->accountTariff->getFirstErrors();
                }
            }

            // Лог тарифов
            Yii::info('AccountTariffForm. Before accountTariffLog->load', 'uu');
            if ($this->accountTariffLog->load($post)) {

                // лог тарифов
                $this->accountTariffLog->account_tariff_id = $this->accountTariff->id;
                $this->accountTariffLog->populateRelation('accountTariff', $this->accountTariff);
                if (isset($post['closeTariff'])) {
                    // закрыть тариф
                    $this->accountTariffLog->tariff_period_id = null;
                } elseif (!$this->accountTariffLog->tariff_period_id) {
                    // если не закрыть, то надо явно установить тариф
                    $this->accountTariffLog->addError('tariff_period_id',
                        Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->accountTariffLog->getAttributeLabel('tariff_period_id')])
                    );
                }

                Yii::info('AccountTariffForm. Before accountTariffLog->save', 'uu');
                if ($this->accountTariffLog->save()) {
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->accountTariffLog->getFirstErrors();
                }

                Yii::info('AccountTariffForm. After accountTariffLog->save', 'uu');
            }

            Yii::info('AccountTariffForm. Before разовая услуга', 'uu');
            if ($post && $isNewRecord && !$this->validateErrors && $this->serviceTypeId == ServiceType::ID_ONE_TIME) {

                // разовая услуга
                $tariffResources = $this->accountTariffLog->tariffPeriod->tariff->tariffResources;
                if (count($tariffResources) != 1) {
                    $this->validateErrors['resourceOneTimeResource'] = 'Неправильные ресурсы у тарифа';
                    throw new InvalidArgumentException();

                }

                $clientDateTime = $this->accountTariffLog->getClientDateTime();
                if ($this->accountTariffLog->actual_from != $clientDateTime->format(DateTimeZoneHelper::DATE_FORMAT)
                ) {
                    $this->validateErrors['resourceOneTimeActualFrom'] = 'Разовая услуга должна действовать с сегодняшнего дня.';
                    $this->accountTariffLog->addError('actual_from', $this->validateErrors['resourceOneTimeActualFrom']);
                    throw new InvalidArgumentException();

                }

                if (!isset($post['resourceOneTimeCost'])) {
                    $this->validateErrors['resourceOneTimeCost'] = 'Не передана стоимость разовой услуги';
                    throw new InvalidArgumentException();

                }

                $resourceOneTimeCost = (float)str_replace(',', '.', $post['resourceOneTimeCost']);
                if (!$resourceOneTimeCost) {
                    $this->validateErrors['resourceOneTimeCost'] = 'Стоимость разовой услуги не может быть нулевой';
                    throw new InvalidArgumentException();
                }

                // сразу же закрыть
                $accountTariffLogClosed = new AccountTariffLog;
                $accountTariffLogClosed->account_tariff_id = $this->accountTariff->id;
                $accountTariffLogClosed->populateRelation('accountTariff', $this->accountTariff);
                $accountTariffLogClosed->tariff_period_id = null;
                $accountTariffLogClosed->actual_from = $clientDateTime->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT);
                if (!$accountTariffLogClosed->save()) {
                    $this->validateErrors += $accountTariffLogClosed->getFirstErrors();
                }

                // собственно, вся услуга заключается в этой стоимости ресурса
                $accountLogResource = new AccountLogResource();
                $accountLogResource->date_from = $accountLogResource->date_to = $this->accountTariffLog->actual_from;
                $accountLogResource->tariff_period_id = $this->accountTariffLog->tariff_period_id;
                $accountLogResource->tariff_resource_id = reset($tariffResources)->id;
                $accountLogResource->account_tariff_id = $this->accountTariff->id;
                $accountLogResource->populateRelation('accountTariff', $this->accountTariff);
                $accountLogResource->amount_use = $resourceOneTimeCost;
                $accountLogResource->amount_free = 0;
                $accountLogResource->amount_overhead = $resourceOneTimeCost;
                $accountLogResource->price_per_unit = 1;
                $accountLogResource->coefficient = 1;
                $accountLogResource->price = $resourceOneTimeCost;
                if (!$accountLogResource->save()) {
                    $this->validateErrors += $accountLogResource->getFirstErrors();
                }
            }

            // Лог ресурсов
            Yii::info('AccountTariffForm. Before AccountTariffResourceLog->load', 'uu');
            if (isset($post['AccountTariffResourceLog'])) {
                $actualFrom = isset($post['AccountTariffResourceLog']['actual_from']) ? $post['AccountTariffResourceLog']['actual_from'] : null;
                foreach ($post['AccountTariffResourceLog'] as $resourceId => $resourceValues) {

                    if (!is_numeric($resourceId)) {
                        continue;
                    }

                    $newResourceValue = $resourceValues['amount'];
                    $currentResourceValue = $this->accountTariff->getResourceValue($resourceId);

                    if ($newResourceValue == $currentResourceValue) {
                        // ресурс не изменился
                        continue;
                    }

                    $accountTariffResourceLog = new AccountTariffResourceLog();
                    $accountTariffResourceLog->account_tariff_id = $this->accountTariff->id;
                    $accountTariffResourceLog->amount = $newResourceValue;
                    $accountTariffResourceLog->resource_id = $resourceId;
                    $accountTariffResourceLog->actual_from = $actualFrom;
                    if ($accountTariffResourceLog->save()) {
                        $this->isSaved = true;
                    } else {
                        $this->validateErrors += $accountTariffResourceLog->getFirstErrors();
                    }
                }

                unset($actualFrom, $resourceId, $resourceValues, $accountTariffResourceLog, $newResourceValue, $currentResourceValue);
            }

            if ($this->validateErrors) {
                throw new InvalidArgumentException();
            }

            Yii::info('AccountTariffForm. Before commit', 'uu');
            $transaction->commit();
            Yii::info('AccountTariffForm. After commit', 'uu');

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;
            if ($isNewRecord) {
                $this->id = $this->accountTariff->id = null;
                $this->accountTariff->setIsNewRecord(true);
            };

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            $this->isSaved = false;

            if (!count($this->validateErrors)) {
                $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
            }

            if ($isNewRecord) {
                $this->id = $this->accountTariff->id = null;
                $this->accountTariff->setIsNewRecord(true);
            };
        }
    }

    /**
     * @return ActiveDataProvider
     */
    public function getAccountTariffLogGrid()
    {
        return new ActiveDataProvider([
            'query' => AccountTariffLog::find()
                ->where('account_tariff_id = :id', ['id' => $this->accountTariff->id])
                ->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
    }

    /**
     * @return ServiceType
     */
    public function getServiceType()
    {
        return ServiceType::findOne($this->serviceTypeId);
    }

    /**
     * @param int $defaultTariffPeriodId
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @return array
     */
    public function getAvailableTariffPeriods(
        &$defaultTariffPeriodId,
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        $accountTariff = $this->accountTariff;
        $accountTariffVoip = $this->accountTariffVoip;
        $clientAccount = $accountTariff->clientAccount;
        $serviceTypeId = $accountTariff->service_type_id ?: $this->serviceTypeId;
        $countryId = $clientAccount->getUuCountryId();

        return TariffPeriod::getList(
            $defaultTariffPeriodId,
            $serviceTypeId,
            $clientAccount->currency,
            $countryId,
            $voipCountryIdTmp = null,
            $accountTariff->city_id,
            $isWithEmpty,
            $isWithNullAndNotNull,
            $statusId = null,
            $clientAccount->is_voip_with_tax,
            $clientAccount->contract->organization_id,
            $accountTariffVoip->voip_ndc_type_id
        );
    }

}