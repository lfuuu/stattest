<?php

namespace app\forms\transfer;

use DateTime;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\classes\Assert;
use app\classes\Form;
use app\classes\transfer\ServiceTransfer;
use app\classes\validators\ArrayValidator;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Transaction;
use app\models\usages\UsageInterface;
use app\models\UsageEmails;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageTechCpe;
use app\models\UsageTrunk;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageWelltime;
use app\models\UsageCallChat;

/**
 * @property ClientAccount $clientAccount
 * @property array $availableUsages
 * @property array $availableAccounts
 * @property array $availableDates
 */
class ServiceTransferForm extends Form
{

    public
        $source_account_id,
        $target_account_id,
        $target_account_id_custom,
        $actual_from = 0,
        $actual_custom,
        $usages = [];

    public
        $clientAccount,
        $availableUsages = [],
        $availableAccounts = [],
        $availableDates = [];

    private static
        $_datesVariants = [
            'first day of next month midnight',
            'first day of next month +1 month midnight',
            'first day of next month +2 month midnight',
        ];

    /** @var ClientAccount|null $targetAccount */
    private $_targetAccount = null;

    /**
     * @param ClientAccount $clientAccount
     */
    public function __construct(ClientAccount $clientAccount)
    {
        parent::__construct();

        $this->clientAccount = $clientAccount;
        $this->source_account_id = $clientAccount->id;
        $this->availableAccounts = $this->_getClientAccountsBySuperClient($clientAccount);
        $this->availableDates = $this->_getActualDateVariants();
        $this->availableUsages = $this->_getAvailableServices();
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['source_account_id'], 'integer'],
            ['usages', ArrayValidator::className()],
            [['target_account_id', 'actual_from',], 'required'],
            [
                'target_account_id_custom',
                'required',
                'when' => function ($model) {
                    return !(int)$model->target_account_id;
                },
                'message' => 'Необходимо заполнить "Лицевой счет"',
            ],
            [
                'actual_from',
                'required',
                'when' => function ($model) {
                    return $model->actual_from !== 'other';
                },
                'message' => 'Необходимо заполнить "Дата переноса"',
            ],
            [
                'actual_custom',
                'required',
                'when' => function ($model) {
                    return $model->actual_from === 'other';
                },
                'message' => 'Необходимо заполнить "Дата переноса"',
            ],
            [
                'actual_custom',
                'date',
                'format' => 'php:' . DateTimeZoneHelper::DATE_FORMAT,
                'when' => function ($model) {
                    return $model->actual_from === 'other';
                },
                'message' => 'Неверный формат даты переноса',
            ],
            ['target_account_id', 'validateTargetAccountId'],
            ['usages', 'validateUsages'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'target_account_id' => 'Лицевой счет',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function validateTargetAccountId()
    {
        try {
            $this->_targetAccount = ClientAccount::findOne(
                (int)(
                $this->target_account_id === 'any' ?
                    $this->target_account_id_custom :
                    $this->target_account_id
                )
            );
            Assert::isObject($this->_targetAccount);
        } catch (\Exception $e) {
            $this->addError('target_account_not_found', 'Выбранный клиент не найден');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateUsages()
    {
        $count = 0;

        /**
         * @var string $serviceKey
         * @var UsageInterface $serviceClass
         */
        foreach (self::getServicesGroups() as $serviceKey => $serviceClass) {
            if (isset($this->usages[$serviceKey]) && is_array($this->usages[$serviceKey])) {
                $count += count($this->usages[$serviceKey]);
            }
        }

        if (!$count) {
            $this->addError('usages_not_selected', 'Необходимо выбрать услуги для переноса');
        }
    }

    /**
     * Процесс переноса услуг
     */
    public function process()
    {
        /**
         * @var string $serviceKey
         * @var ActiveRecord $serviceClass
         */
        foreach (self::getServicesGroups() as $serviceKey => $serviceClass) {
            if (!isset($this->usages[$serviceKey]) || !is_array($this->usages[$serviceKey])) {
                continue;
            }

            foreach ($this->usages[$serviceKey] as $usageId) {
                /** @var ServiceTransfer $serviceTransfer */
                $serviceTransfer = $serviceClass::getTransferHelper($serviceClass::findOne($usageId))
                        ->setTargetAccount($this->_targetAccount)
                        ->setActivationDate(
                            $this->actual_from === 'other' ?
                                $this->actual_custom :
                                $this->actual_from
                        );

                try {
                    try {
                        $serviceTransfer->process();
                    } catch (\yii\base\InvalidValueException $e) {
                        $this->addError('not_transfer_usage_' . $usageId, $usageId . ': ' . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    \Yii::error($e);
                }

                $serviceTransfer->trigger(static::EVENT_AFTER_SAVE, new ModelEvent);
            }
        }

        return $this->getErrors() ? false : true;
    }

    /**
     * Список возможных услуг
     *
     * @return array
     */
    public static function getServicesGroups()
    {
        return [
            Transaction::SERVICE_EMAIL => UsageEmails::className(),
            Transaction::SERVICE_EXTRA => UsageExtra::className(),
            Transaction::SERVICE_SMS => UsageSms::className(),
            Transaction::SERVICE_WELLTIME => UsageWelltime::className(),
            Transaction::SERVICE_VIRTPBX => UsageVirtpbx::className(),
            Transaction::SERVICE_VOIP => UsageVoip::className(),
            Transaction::SERVICE_TRUNK => UsageTrunk::className(),
            Transaction::SERVICE_IPPORT => UsageIpPorts::className(),
            Transaction::SERVICE_TECH_CPE => UsageTechCpe::className(),
            Transaction::SERVICE_CALL_CHAT => UsageCallChat::className(),
        ];
    }

    /**
     * @return array
     */
    private function _getAvailableServices()
    {
        $result = [];

        /**
         * @var string $serviceKey
         * @var UsageInterface $serviceClass
         */
        foreach (self::getServicesGroups() as $serviceKey => $serviceClass) {
            /** @var ServiceTransfer $helper */
            $helper = $serviceClass::getTransferHelper();
            $usages = $helper->getPossibleToTransfer($this->clientAccount);

            if (count($usages)) {
                $result[$serviceKey] = [
                    'title' => reset($usages)->helper->title,
                    'usages' => ArrayHelper::map($usages, 'id', 'helper'),
                ];
            }
        }

        return $result;
    }

    /**
     * Получение всех лицевых счетов супер-клиента
     *
     * @param ClientAccount $clientAccount
     * @return ClientAccount[]
     */
    private function _getClientAccountsBySuperClient(ClientAccount $clientAccount)
    {
        $accounts = ClientAccount::find()
                ->andWhere(['super_id' => $clientAccount->super_id])
                ->andWhere(['!=', 'id', $clientAccount->id])
                ->orderBy('contract_id ASC, id ASC')
                ->each();

        $result = [];
        foreach ($accounts as $account) {
            $result[$account->id] = '№' . $account->id . ' - ' . $account->contract->contragent->name;
        }

        $this->target_account_id = reset(array_keys($result));

        return $result;
    }

    /**
     * Получение списка доступных для переноса дат
     *
     * @return array
     */
    private function _getActualDateVariants()
    {
        $result = [];

        foreach (self::$_datesVariants as $variant) {
            $date = (new DateTime($variant))->format(DateTimeZoneHelper::DATE_FORMAT);
            $result[$date] = $date;
        }

        $this->actual_from = reset($result);

        return $result;
    }

}
