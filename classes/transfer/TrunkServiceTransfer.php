<?php

namespace app\classes\transfer;

use Yii;
use yii\base\InvalidValueException;
use app\models\UsageTrunkSettings;

/**
 * Класс переноса услуги "Телефония транки"
 * @package app\classes\transfer
 */
class TrunkServiceTransfer extends ServiceTransfer
{

    /**
     * Перенос базовой сущности услуги
     * @return object - созданная услуга
     */
    public function process()
    {
        if ((int) $this->service->next_usage_id)
            throw new InvalidValueException('Услуга уже перенесена');

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
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
        }
        catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }

        $this->processSettings($targetService);

        return $targetService;
    }

    /**
     * Перенос связанных с услугой настроек
     * @param object $targetService - базовая услуга
     */
    private function processSettings($targetService)
    {
        foreach ($this->service->settings as $setting) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                $targetSetting = new $setting;
                $targetSetting->setAttributes($setting->getAttributes(), false);
                unset($targetSetting->id);
                $targetSetting->usage_id = $targetService->id;

                $targetSetting->save();

                $dbTransaction->commit();
            }
            catch (\Exception $e) {
                $dbTransaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Процесс отмены переноса услуги, в простейшем варианте, только манипуляции с записями
     */
    public function fallback()
    {
        $this->fallbackSettings();

        parent::fallback();
    }

    /**
     * Отмена переноса связанных с услугой настроек
     */
    private function fallbackSettings()
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
