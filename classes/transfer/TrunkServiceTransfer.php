<?php

namespace app\classes\transfer;

use Yii;
use yii\base\InvalidValueException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\UsageTrunk;
use yii\db\ActiveRecord;

/**
 * Класс переноса услуги "Телефония транки"
 */
class TrunkServiceTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageTrunk[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        $now = new \DateTime();

        return
            UsageTrunk::find()
                ->andWhere(['client_account_id' => $clientAccount->id])
                ->andWhere(['<=', 'actual_from', $now->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

    /**
     * Процесс переноса услуги
     *
     * @return ActiveRecord
     * @throws \Exception
     */
    public function process()
    {
        if ((int)$this->service->next_usage_id) {
            throw new InvalidValueException('Услуга уже перенесена');
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            /** @var ActiveRecord $targetService */
            $targetService = new $this->service;
            $targetService->setAttributes($this->service->getAttributes(), false);
            unset($targetService->id);
            $targetService->actual_from = $this->getActualDate();
            $targetService->prev_usage_id = $this->service->id;
            $targetService->client_account_id = $this->targetAccount->id;

            $targetService->save();

            $this->service->actual_to = $this->getExpireDate();
            $this->service->next_usage_id = $targetService->id;

            $this->service->save();

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }

        $this->_processSettings($targetService);

        return $targetService;
    }

    /**
     * Процесс переноса настроек услуги
     *
     * @param ActiveRecord $targetService
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function _processSettings($targetService)
    {
        foreach ($this->service->settings as $setting) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                /** @var ActiveRecord $targetSetting */
                $targetSetting = new $setting;
                $targetSetting->setAttributes($setting->getAttributes(), false);
                unset($targetSetting->id);
                $targetSetting->usage_id = $targetService->id;

                $targetSetting->save();

                $dbTransaction->commit();
            } catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Процесс отмены переноса услуги
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function fallback()
    {
        $this->_fallbackSettings();

        parent::fallback();
    }

    /**
     * Процесс отмены переноса настроек услуги
     */
    private function _fallbackSettings()
    {
        /*
        $settings =
            UsageTrunkSettings::find()
                ->andWhere(['usage_id' => $this->service->id])
                ->all();

        foreach ($settings as $setting) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                $movedSettings =
                    UsageTrunkSettings::find()
                        ->andWhere(['usage_id' => $this->service->next_usage_id])
                        ->andWhere('actual_from > :date', [':date' => (new \DateTime())->format('Y-m-d')])
                        ->one();
                Assert::isObject($movedSettings);

                $setting->actual_to = $movedSettings->actual_to;
                $setting->save();

                $movedSettings->delete();
                $dbTransaction->commit();
            }
            catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }*/
    }

}
