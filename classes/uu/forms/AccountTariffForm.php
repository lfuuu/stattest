<?php

namespace app\classes\uu\forms;

use app\classes\Form;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\AccountTariffVoip;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffPeriod;
use DateTime;
use InvalidArgumentException;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Class AccountTariffForm
 * @property bool IsNeedToSelectClient
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
     * показывать ли предупреждение, что необходимо выбрать клиента
     * @return bool
     */
    abstract public function getIsNeedToSelectClient();

    /**
     * конструктор
     */
    public function init()
    {
        $this->accountTariff = $this->getAccountTariffModel();

        $this->accountTariffLog = new AccountTariffLog();
        $this->accountTariffLog->actual_from = (new DateTime())->modify('+1 day')->format('Y-m-d');

        if ($this->serviceTypeId === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter usage type'));
        }

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    public function loadFromInput()
    {
        // загрузить параметры от юзера
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var array $post */
            $post = Yii::$app->request->post();

            // при создании услуга + лог в одной форме, при редактировании - в разных
            $isNewRecord = $this->accountTariff->isNewRecord;


            // при создании услуги телефонии свой интерфейс, из которого данные надо преобразовать в нужный формат
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
                    $accountTariff = clone $this->accountTariff;
                    $accountTariff->voip_number = $voipNumber;
                    $accountTariff->id = 0;
                    if (!$accountTariff->validate() || !$accountTariff->save()) {
                        $this->validateErrors += $accountTariff->getFirstErrors();
                        throw new InvalidArgumentException('');
                    }

                    // лог тарифов
                    $accountTariffLog = clone $this->accountTariffLog;
                    $accountTariffLog->account_tariff_id = $accountTariff->id;
                    $accountTariffLog->id = 0;
                    if (!$accountTariffLog->validate() || !$accountTariffLog->save()) {
                        $this->validateErrors += $accountTariffLog->getFirstErrors();
                        throw new InvalidArgumentException('');
                    }

                    // пакеты
                    foreach ($accountTariffVoip->voip_package_tariff_period_ids as $voipPackageTariffPeriodId) {
                        // услуга
                        $accountTariffPackage = new AccountTariff;
                        $accountTariffPackage->client_account_id = $accountTariff->client_account_id;
                        $accountTariffPackage->service_type_id = ServiceType::ID_VOIP_PACKAGE;
                        $accountTariffPackage->region_id = $accountTariff->region_id;
                        $accountTariffPackage->city_id = $accountTariff->city_id;
                        $accountTariffPackage->prev_account_tariff_id = $accountTariff->id;
                        if (!$accountTariffPackage->validate() || !$accountTariffPackage->save()) {
                            $this->validateErrors += $accountTariffPackage->getFirstErrors();
                            throw new InvalidArgumentException('');
                        }

                        // лог тарифов
                        $accountTariffLogPackage = new AccountTariffLog;
                        $accountTariffLogPackage->account_tariff_id = $accountTariffPackage->id;
                        $accountTariffLogPackage->tariff_period_id = $voipPackageTariffPeriodId;
                        $accountTariffLogPackage->actual_from = $accountTariffLog->actual_from;
                        if (!$accountTariffLogPackage->validate() || !$accountTariffLogPackage->save()) {
                            $this->validateErrors += $accountTariffLogPackage->getFirstErrors();
                            throw new InvalidArgumentException('');
                        }
                    }
                }

                $this->isSaved = true;

                $this->accountTariffLog->account_tariff_id = 0; // вернуть обратно. 0 - потому что $isNewRecord
                $post = []; // чтобы дальше логика универсальных услуг не отрабатывала
            }

            if ($this->accountTariff->load($post)) {

                // услуга
                if ($this->accountTariff->save()) {
                    $this->id = $this->accountTariff->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->accountTariff->getFirstErrors();
                }

            }

            if ($this->accountTariffLog->load($post)) {

                // лог тарифов
                $this->accountTariffLog->account_tariff_id = $this->accountTariff->id;
                if (isset($post['closeTariff'])) {
                    // закрыть тариф
                    $this->accountTariffLog->tariff_period_id = null;
                } elseif (!$this->accountTariffLog->tariff_period_id) {
                    // если не закрыть, то надо явно установить тариф
                    $this->accountTariffLog->addError('tariff_period_id',
                        Yii::t('yii', '{attribute} cannot be blank.', [
                            'attribute' => $this->accountTariffLog->getAttributeLabel('tariff_period_id')
                        ])
                    );
                }
                if ($this->accountTariffLog->validate(null, false)) {
                    $this->accountTariffLog->save();

                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->accountTariffLog->getFirstErrors();
                }

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
                        $accountTariffLogPackage->tariff_period_id = null;
                        $accountTariffLogPackage->actual_from = $this->accountTariffLog->actual_from;
                        if (!$accountTariffLogPackage->save()) {
                            $this->validateErrors += $accountTariffLogPackage->getFirstErrors();
                        }

                    }
                }

            }

            if ($this->validateErrors) {
                if ($isNewRecord) {
                    $this->id = $this->accountTariff->id = null;
                    $this->accountTariff->setIsNewRecord(true);
                };
                throw new InvalidArgumentException();
            }

            $transaction->commit();

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            $this->isSaved = false;
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
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
                ->orderBy(['actual_from' => SORT_DESC, 'id' => SORT_DESC]),
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
     * @return []
     */
    public function getAvailableTariffPeriods(
        &$defaultTariffPeriodId,
        $isWithEmpty = false,
        $serviceTypeId = null,
        $cityId = null
    ) {
        return TariffPeriod::getList($defaultTariffPeriodId, $serviceTypeId ?: $this->serviceTypeId,
            $this->accountTariff->clientAccount->currency, $cityId, $isWithEmpty);
    }

}