<?php

namespace app\classes\transfer;
use app\models\UsageTrunkSettings;

/**
 * Класс переноса услуги "Телефония транки"
 * @package app\classes\transfer
 */
class TrunkServiceTransfer extends ServiceTransfer
{

    /**
     * Перенос базовой сущности услуги
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return object - созданная услуга
     */
    public function process(ClientAccount $targetAccount)
    {
        $targetService = parent::process($targetAccount);

        $this->processSettings($targetService);

        Event::go('ats2_numbers_check');

        return $targetService;
    }

    /**
     * Перенос связанных с услугой настроек
     * @param object $targetService - базовая услуга
     */
    private function processSettings($targetService)
    {
        $settings =
            UsageTrunkSettings::find()
                ->andWhere(['usage_id' => $this->service->id])
                -all();

        foreach ($settings as $setting) {
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
        parent::fallback();

        $this->fallbackSettings();
    }

    /**
     * Отмена переноса связанных с услугой настроек
     */
    public function fallbackSettings()
    {
        $settings =
            UsageTrunkSettings::find()
                ->andWhere(['usage_id' => $this->service->id])
                ->all();

        foreach ($settings as $setting) {
            $dbTransaction = Yii::$app->db->beginTransaction();
            try {
                $movedSettings =
                    UsageTrunkSettings::find()
                        ->andWhere(['src_usage_id' => $setting->id])
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
        }

    }

}