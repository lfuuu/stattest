<?php

namespace app\modules\sim\forms\registry;

use app\modules\sim\models\Registry;
use app\exceptions\ModelValidationException;
use app\modules\sim\classes\RegistryState;
use app\modules\sim\models\RegionSettings;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiPartner;
use app\modules\sim\models\ImsiProfile;
use yii\base\InvalidArgumentException;

class Form extends \app\classes\Form
{
    public
        $id = 0,
        $region_sim_settings_id,
        $iccid_from,
        $iccid_to,
        $imsi_from,
        $imsi_to,
        $imsi_s1_from,
        $imsi_s1_to,
        $imsi_s2_from,
        $imsi_s2_to
    ;

    /** @var Registry */
    public $registry = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ( $this->id && !( $this->registry = Registry::findOne(['id' => $this->id]) )) {
            throw new InvalidArgumentException('Неверная заливка');
        }
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'region_sim_settings_id'], 'integer'],
            [['iccid_from', 'iccid_to'], 'string', 'max' => 16],
            [['imsi_from', 'imsi_to'], 'string', 'max' => 16],
            [['imsi_s1_from', 'imsi_s1_to'], 'string', 'min' => 15, 'max' => 15],
            [['imsi_s2_from', 'imsi_s2_to'], 'string', 'min' => 15, 'max' => 15],
        ];
    }

    /**
     * Вернуть имена полей
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'region_sim_settings_id' => 'Настройки региона',

            'iccid_from' => 'ICCID с',
            'iccid_to' => 'ICCID по',

            'imsi_from' => 'IMSI с',
            'imsi_to' => 'IMSI по',

            'imsi_s1_from' => 'IMSI S1 с',
            'imsi_s1_to' => 'IMSI S1 по',

            'imsi_s2_from' => 'IMSI S2 с',
            'imsi_s2_to' => 'IMSI S2 по',
        ];
    }

    /**
     * Попробовать сохранить, если POST
     *
     * @return bool
     * @throws ModelValidationException
     */
    public function tryToSave()
    {
        $result = false;

        $this->registry = Registry::findOne(['id' => $this->id]);

        $post = \Yii::$app->request->post();
        if (!empty($post['save'])) {
            if (!$this->registry) {
                $this->registry = new Registry();

                $this->registry->state = RegistryState::NEW;
            }

            $this->load($post);

            // set fields
            if ($regionSettings = RegionSettings::findOne(['id' => $this->region_sim_settings_id])) {
                /** @var RegionSettings $regionSettings */
                $this->registry->region_sim_settings_id = $regionSettings->id;
            }

            foreach ([
                         'iccid_from',
                         'iccid_to',
                         'imsi_from',
                         'imsi_to',
                         'imsi_s1_from',
                         'imsi_s1_to',
                         'imsi_s2_from',
                         'imsi_s2_to',
                     ] as $field) {
                $this->registry->{$field} = $this->{$field};
            }

            $this->registry->count = (int)$this->iccid_to - (int)$this->iccid_from + 1;

            // saving
            $result = $this->registry->validate();
            $result = $result && $this->registry->validateRages();
            $result = $result && $this->registry->save();
            if (!$result) {
                throw new ModelValidationException($this->registry);
            }

            if ($result) {
                $this->id = $this->registry->id;
            }
        }

        return $result;
    }

    /**
     * Изменение статуса с проверкой
     *
     * @param int $id
     * @param int $stateFrom
     * @param int $stateTo
     * @param string $errorMessage
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected static function changeState($id, $stateFrom, $stateTo, $errorMessage)
    {
        $model = Registry::findOne(['id' => $id]);
        if (!$model) {
            throw new InvalidArgumentException('Неверная заливка');
        }

        /** @var Registry $model */
        if ($model->state != $stateFrom) {
            $errorMessage = strtr($errorMessage, ['{state}' => $model->stateName]);

            throw new InvalidArgumentException($errorMessage);
        }

        $model->state = $stateTo;
        if (!$model->save()) {
            throw new ModelValidationException($model);
        }

        return $id;
    }

    /**
     * Start
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function start($id)
    {
        return self::changeState(
            $id,
            RegistryState::NEW,
            RegistryState::STARTED,
            'Заливка в статусе {state} не может быть запущена.'
        );
    }

    /**
     * Cancel
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function cancel($id)
    {
        return self::changeState(
            $id,
            RegistryState::NEW,
            RegistryState::CANCELLED,
            'Заливка в статусе {state} не может быть отменена.'
        );
    }

    /**
     * Restore
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function restore($id)
    {
        return self::changeState(
            $id,
            RegistryState::CANCELLED,
            RegistryState::NEW,
            'Заливка в статусе {state} не может быть восстановлена.'
        );
    }

    /**
     * @param Registry $model
     * @return bool
     * @throws ModelValidationException
     * @throws \yii\db\Exception
     */
    public static function process(Registry $model)
    {
        $model->state = RegistryState::PROCESSING;
        $model->save();

        $model->createLog();
        $model->save();

        $transaction = Registry::getDb()->beginTransaction();
        $transactionPg = Imsi::getDb()->beginTransaction();
        try {
            $isValid = $model->validate() && $model->validateRages();
            if (!$isValid) {
                throw new ModelValidationException($model);
            }
            self::processImsi($model);

            $transaction->commit();
            $transactionPg->commit();
        } catch (\Exception $e) {
            $transactionPg->rollBack();

            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            $model->state = RegistryState::ERROR;
            $errorText = sprintf(
                'Ошибка обработчика заливки сим-карт: %s',
                $e->getMessage()
            );

            $model->addErrorText($errorText);
            if ($model->validate() && !$model->save()) {
                throw new ModelValidationException($model);
            }

            return false;
        }

        return true;
    }

    protected static function processImsi(Registry $model)
    {
        $model->state = RegistryState::COMPLETED;
        $model->save();

        if ($model->count) {
            $iccidFrom = intval($model->getICCIDFromFull());

            $imsiFrom = intval($model->getIMSIFromFull());
            $imsiS1From = intval($model->imsi_s1_from);
            $imsiS2From = intval($model->imsi_s2_from);

            $batchInsertValues = [];
            for ($i = 0; $i < $model->count; $i++) {
                $iccidCurrent = $iccidFrom + $i;

                $imsiModel = new Imsi();
                $imsiModel->imsi = $imsiFrom + $i;
                $imsiModel->iccid = $iccidCurrent;

                $imsiModel->is_anti_cli = 0;
                $imsiModel->is_roaming = 0;
                $imsiModel->is_active = 1;

                $imsiModel->partner_id = ImsiPartner::ID_TELE2;
                $imsiModel->is_default = 1;
                $imsiModel->profile_id = ImsiProfile::ID_MSN_RUS;
                //$imsiModel->save();
                $batchInsertValues[] = $imsiModel->toArray();

                if ($imsiS1From) {
                    // s1
                    $imsiModelS1 = new Imsi();
                    $imsiModelS1->imsi = $imsiS1From + $i;
                    $imsiModelS1->iccid = $iccidCurrent;

                    $imsiModelS1->is_anti_cli = 0;
                    $imsiModelS1->is_roaming = 0;
                    $imsiModelS1->is_active = 1;

                    $imsiModelS1->partner_id = ImsiPartner::ROAMABILITY;
                    $imsiModelS1->is_default = 0;
                    $imsiModelS1->profile_id = ImsiProfile::ID_S1;
                    //$imsiModelS1->save();
                    $batchInsertValues[] = $imsiModelS1->toArray();
                }

                if ($imsiS2From) {
                    // s2
                    $imsiModelS2 = new Imsi();
                    $imsiModelS2->imsi = $imsiS2From + $i;
                    $imsiModelS2->iccid = $iccidCurrent;

                    $imsiModelS2->is_anti_cli = 0;
                    $imsiModelS2->is_roaming = 0;
                    $imsiModelS2->is_active = 1;

                    $imsiModelS2->partner_id = ImsiPartner::ROAMABILITY;
                    $imsiModelS2->is_default = 0;
                    $imsiModelS2->profile_id = ImsiProfile::ID_S2;
                    //$imsiModelS2->save();
                    $batchInsertValues[] = $imsiModelS2->toArray();
                }

            }

            self::batchInsertValues($batchInsertValues);

            $rs = $model->regionSettings;
            $rs->iccid_last_used = $model->iccid_to;
            $rs->save();
        }
    }

    /**
     * @param $batchInsertValues
     * @throws \yii\db\Exception
     */
    protected static function batchInsertValues($batchInsertValues)
    {
        if (count($batchInsertValues)) {
            $fields = array_keys(current($batchInsertValues));
            Imsi::getDb()->createCommand()->batchInsert(
                Imsi::tableName(),
                $fields,
                $batchInsertValues
            )->execute();
        }
    }
}