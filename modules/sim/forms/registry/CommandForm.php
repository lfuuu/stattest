<?php

namespace app\modules\sim\forms\registry;

use app\classes\Form;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Registry;
use app\exceptions\ModelValidationException;
use app\modules\sim\classes\RegistryState;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiPartner;
use app\modules\sim\models\ImsiProfile;
use yii\base\InvalidArgumentException;

class CommandForm extends Form
{
    public int $countICCIDs = 0;

    public int $countIMSIs = 0;

    public string $errorText = '';

    /** @var Registry */
    public $registry = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ( !$this->registry ) {
            throw new InvalidArgumentException('Отсутствует заливка');
        }
    }

    /**
     * @return bool
     * @throws ModelValidationException
     * @throws \yii\db\Exception
     */
    public function process()
    {
        $model = $this->registry;

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

            $this->processImsi();

            $transaction->commit();
            $transactionPg->commit();
        } catch (\Exception $e) {
            $transactionPg->rollBack();

            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            $this->errorText = $e->getMessage();

            $model->state = RegistryState::ERROR;
            $errorText = sprintf(
                'Ошибка обработчика заливки SIM-карт: %s',
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

    /**
     * @throws \yii\db\Exception
     */
    protected function processImsi()
    {
        $model = $this->registry;

        $model->state = RegistryState::COMPLETED;
        $model->save();

        if ($model->count) {
            $iccidFrom = intval($model->getICCIDFromFull());

            $imsiFrom = intval($model->getIMSIFromFull());
            $imsiS1From = intval($model->imsi_s1_from);
            $imsiS2From = intval($model->imsi_s2_from);

            $batchInsertIMSIs = [];
            $batchInsertICCIDs = [];
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
                $batchInsertIMSIs[] = $imsiModel->toArray();

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
                    $batchInsertIMSIs[] = $imsiModelS1->toArray();
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
                    $batchInsertIMSIs[] = $imsiModelS2->toArray();
                }

                $simCard = new Card();
                $simCard->iccid = $iccidCurrent;
                $simCard->is_active = 1;
                $simCard->status_id = CardStatus::ID_DEFAULT;
                $simCard->region_id = $model->regionSettings->region_id;
                //$simCard->save();
                $batchInsertICCIDs[] = $simCard->toArray();
            }

            $this->batchInsertCard($batchInsertICCIDs);
            $this->batchInsertIMSI($batchInsertIMSIs);

            $this->countICCIDs = count($batchInsertICCIDs);
            $this->countIMSIs = count($batchInsertIMSIs);

            $rs = $model->regionSettings;
            $rs->iccid_last_used = $model->iccid_to;
            $rs->imsi_last_used = $model->imsi_to;
            $rs->save();
        }
    }

    /**
     * @param $batchInsertValues
     * @throws \yii\db\Exception
     */
    protected function batchInsertIMSI($batchInsertValues)
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

    /**
     * @param $batchInsertValues
     * @throws \yii\db\Exception
     */
    protected function batchInsertCard($batchInsertValues)
    {
        if (count($batchInsertValues)) {
            $fields = array_keys(current($batchInsertValues));

            Card::getDb()->createCommand()->batchInsert(
                Card::tableName(),
                $fields,
                $batchInsertValues
            )->execute();
        }
    }
}