<?php

namespace app\classes\uu\forms;

use app\classes\Form;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
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
            $post = Yii::$app->request->post();

            // при создании услуга + лог в одной форме, при редактировании - в разных

            $isNewRecord = $this->accountTariff->isNewRecord;

            if ($this->accountTariff->load($post)) {

                // услуга
                if ($this->accountTariff->validate()) {
                    $this->accountTariff->save();
                    $this->id = $this->accountTariff->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->tariff->getFirstErrors();
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

                    $this->accountTariff->tariff_period_id = $this->accountTariffLog->tariff_period_id;
                    $this->accountTariff->save();

                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->accountTariffLog->getFirstErrors();
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
     * @return TariffPeriod[]
     */
    public function getAvailableTariffPeriods($isWithEmpty = false)
    {
        return TariffPeriod::getList($this->serviceTypeId, $this->accountTariff->clientAccount->currency, $isWithEmpty);
    }

}