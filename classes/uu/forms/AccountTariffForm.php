<?php

namespace app\classes\uu\forms;

use app\classes\Form;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\AccountTariffVoip;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffPeriod;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\usages\UsageInterface;
use app\models\UsageTrunk;
use InvalidArgumentException;
use LogicException;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Class AccountTariffForm
 */
abstract class AccountTariffForm extends Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var int */
    public $serviceTypeId;

    /** @var AccountTariff */
    public $accountTariff;

    /** @var AccountTariffLog */
    public $accountTariffLog;

    /** @var string[] */
    public $validateErrors = [];

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
     */
    public function init()
    {
        $this->accountTariff = $this->getAccountTariffModel();

        if ($this->serviceTypeId === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter usage type'));
        }

        $this->accountTariffLog = new AccountTariffLog();
        $this->accountTariffLog->account_tariff_id = $this->accountTariff->id;
        $this->accountTariffLog->populateRelation('accountTariff', $this->accountTariff);
        $this->accountTariffLog->actual_from = $this->accountTariffLog
            ->getClientDateTime()
            // ->modify($this->serviceTypeId == ServiceType::ID_ONE_TIME ? '+0 day' : '+1 day')
            ->format(DateTimeZoneHelper::DATE_FORMAT);

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    public function loadFromInput()
    {
        /** @var array $post */
        $post = Yii::$app->request->post();
        if (!$post) {
            return;
        }

        $transaction = \Yii::$app->db->beginTransaction();

        // при создании услуга + лог в одной форме, при редактировании - в разных
        $isNewRecord = $this->accountTariff->isNewRecord;

        try {

            // при создании услуги телефонии свой интерфейс, из которого данные надо преобразовать в нужный формат
            Yii::info('AccountTariffForm. Before accountTariffVoip', 'uu');
            $accountTariffVoip = new AccountTariffVoip();
            if ($isNewRecord && $this->serviceTypeId == ServiceType::ID_VOIP
                && $accountTariffVoip->load($post)
                && $this->accountTariffLog->load($post)
            ) {

                if (!$accountTariffVoip->validate()) {
                    $this->validateErrors += $accountTariffVoip->getFirstErrors();
                    throw new InvalidArgumentException('');
                }

                $this->accountTariff->city_id = $accountTariffVoip->city_id;

                // каждый выбранный номер телефона - отдельная услуга
                foreach ($accountTariffVoip->voip_numbers as $voipNumber) {

                    // услуга
                    Yii::info('AccountTariffForm. Before voip $accountTariff->save', 'uu');
                    $accountTariff = clone $this->accountTariff;
                    $accountTariff->voip_number = $voipNumber;
                    $accountTariff->id = 0;
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

                    // пакеты
                    foreach ($accountTariffVoip->voip_package_tariff_period_ids as $voipPackageTariffPeriodId) {
                        // услуга
                        Yii::info('AccountTariffForm. Before voip $accountTariffPackage->save', 'uu');
                        $accountTariffPackage = new AccountTariff;
                        $accountTariffPackage->client_account_id = $accountTariff->client_account_id;
                        $accountTariffPackage->service_type_id = ServiceType::ID_VOIP_PACKAGE;
                        $accountTariffPackage->region_id = $accountTariff->region_id;
                        $accountTariffPackage->city_id = $accountTariff->city_id;
                        $accountTariffPackage->prev_account_tariff_id = $accountTariff->id;
                        $accountTariffPackage->populateRelation('prevAccountTariff', $accountTariff);
                        if (!$accountTariffPackage->save()) {
                            $this->validateErrors += $accountTariffPackage->getFirstErrors();
                            throw new ModelValidationException($accountTariffPackage);
                        }

                        // лог тарифов
                        Yii::info('AccountTariffForm. Before voip $accountTariffLogPackage->save', 'uu');
                        $accountTariffLogPackage = new AccountTariffLog;
                        $accountTariffLogPackage->account_tariff_id = $accountTariffPackage->id;
                        $accountTariffLogPackage->populateRelation('accountTariff', $accountTariffPackage);
                        $accountTariffLogPackage->tariff_period_id = $voipPackageTariffPeriodId;
                        $accountTariffLogPackage->actual_from = $accountTariffLog->actual_from;
                        if (!$accountTariffLogPackage->save()) {
                            $this->validateErrors += $accountTariffLogPackage->getFirstErrors();
                            throw new ModelValidationException($accountTariffLogPackage);
                        }

                        Yii::info('AccountTariffForm. After voip $accountTariffLogPackage->save', 'uu');
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

                // услуга
                if ($this->accountTariff->save()) {
                    $this->id = $this->accountTariff->id;
                    $this->isSaved = true;

                    if ($this->accountTariff->service_type_id == ServiceType::ID_TRUNK && isset($post['trunkId'])) {
                        // isset($post['trunkId']) гарантирует, что сюда попадаем только при создании, но не при редактировании
                        if (!(int)$post['trunkId']) {
                            throw new LogicException('Не указан транк');
                        }

                        // дополнительно создать "логический транк"
                        $usageTrunk = new UsageTrunk;
                        $usageTrunk->id = $this->accountTariff->id;
                        $usageTrunk->client_account_id = $this->accountTariff->client_account_id;
                        $usageTrunk->connection_point_id = $this->accountTariff->region_id;
                        $usageTrunk->trunk_id = (int)$post['trunkId'];
                        $usageTrunk->actual_from = date(DateTimeZoneHelper::DATE_FORMAT);
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
                if ($this->accountTariffLog->validate(null, false) && $this->accountTariffLog->save()) {
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->accountTariffLog->getFirstErrors();
                }

                Yii::info('AccountTariffForm. After accountTariffLog->save', 'uu');

                if (isset($post['closeTariff'])) {
                    // если закрывается услуга, то надо закрыть и все пакеты
                    foreach ($this->accountTariff->nextAccountTariffs as $accountTariffPackage) {

                        if (!$accountTariffPackage->tariff_period_id) {
                            // эта услуга и так закрыта
                            continue;
                        }

                        // записать в лог тарифа
                        $accountTariffLogPackage = new AccountTariffLog;
                        $accountTariffLogPackage->account_tariff_id = $accountTariffPackage->id;
                        $accountTariffLogPackage->populateRelation('accountTariff', $accountTariffPackage);
                        $accountTariffLogPackage->tariff_period_id = null;
                        $accountTariffLogPackage->actual_from = $this->accountTariffLog->actual_from;
                        if (!$accountTariffLogPackage->save()) {
                            $this->validateErrors += $accountTariffLogPackage->getFirstErrors();
                        }
                    }
                }
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
                $accountLogResource->date = $this->accountTariffLog->actual_from;
                $accountLogResource->tariff_period_id = $this->accountTariffLog->tariff_period_id;
                $accountLogResource->tariff_resource_id = reset($tariffResources)->id;
                $accountLogResource->account_tariff_id = $this->accountTariff->id;
                $accountLogResource->populateRelation('accountTariff', $this->accountTariff);
                $accountLogResource->amount_use = $resourceOneTimeCost;
                $accountLogResource->amount_free = 0;
                $accountLogResource->amount_overhead = $resourceOneTimeCost;
                $accountLogResource->price_per_unit = 1;
                $accountLogResource->price = $resourceOneTimeCost;
                $accountLogResource->save();
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
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
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
                ->orderBy(['actual_from_utc' => SORT_DESC, 'id' => SORT_DESC]),
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
     * @param int $serviceTypeId
     * @param int $cityId
     * @param bool $isWithNullAndNotNull
     * @return array
     */
    public function getAvailableTariffPeriods(
        &$defaultTariffPeriodId,
        $isWithEmpty = false,
        $serviceTypeId = null,
        $cityId = null,
        $isWithNullAndNotNull = false
    ) {
        return TariffPeriod::getList($defaultTariffPeriodId, $serviceTypeId ?: $this->serviceTypeId,
            $this->accountTariff->clientAccount->currency, $cityId, $isWithEmpty, $isWithNullAndNotNull);
    }

}