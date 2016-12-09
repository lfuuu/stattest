<?php

namespace app\classes\transfer;

use Yii;
use app\classes\Assert;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\UsageVoipPackage;
use app\classes\uu\model\Tariff;
use app\models\usages\UsageInterface;
use yii\db\ActiveRecord;

/**
 * Класс переноса услуг типа "Телефония номера"
 * @package app\classes\transfer
 */
class VoipServiceTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageVoip[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        $usages =
            UsageVoip::find()
                ->client($clientAccount->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->orderBy(['id' => SORT_DESC]);

        $voipNumbers = $clientAccount->voipNumbers;
        $stopList = [];

        foreach ($voipNumbers as $number => $options) {
            if ($options['type'] !== 'vpbx' || !$options['stat_product_id']) {
                continue;
            }
            $stopList[] = $number;
        }

        $result = [];
        if ($usages->count()) {
            foreach ($usages->each() as $usage) {
                if (
                    in_array($usage->E164, $stopList)
                        ||
                    (
                        $usage->type_id === Tariff::NUMBER_TYPE_LINE &&
                        UsageVoip::find()->where(['line7800_id' => $usage->id])->count()
                    )
                ) {
                    continue;
                }
                $result[] = $usage;
            }
        }

        return $result;
    }

    /**
     * Процесс переноса
     *
     * @return UsageInterface
     * @throws \Exception
     */
    public function process()
    {
        $targetService = parent::process();

        LogTarifTransfer::process($this, $targetService->id);

        $this->process7800($targetService);
        $this->processPackages($targetService);

        return $targetService;
    }

    /**
     * Процесс отмены переноса услуги
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function fallback()
    {
        LogTarifTransfer::fallback($this);

        parent::fallback();

        $this->fallback7800();
        $this->fallbackPackages();
    }

    /**
     * Перенос связанных с услугой линий без номер, если услуга 7800
     *
     * @param ActiveRecord $targetService
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    private function process7800($targetService)
    {
        if (!$targetService->line7800_id) {
            return;
        }

        $line7800 = UsageVoip::findOne($targetService->line7800_id);
        Assert::isObject($line7800);

        $this->service = $line7800;
        $targetService7800 = parent::process();
        $targetService->line7800_id = $targetService7800->id;
        $targetService->save();

        LogTarifTransfer::process($this, $targetService7800->id);
    }

    /**
     * Перенос связанных с услугой пакетов
     *
     * @param ActiveRecord $targetService
     */
    private function processPackages($targetService)
    {
        $packages =
            UsageVoipPackage::find()
                ->andWhere(['usage_voip_id' => $this->service->id])
                ->andWhere(['<=', 'actual_from', $this->getExpireDate()])
                ->andWhere(['>=', 'actual_to', $this->getExpireDate()])
                ->all();

        if (!count($packages)) {
            return;
        }

        foreach ($packages as $package) {
            $package->transferHelper
                ->setUsageVoip($targetService)
                ->setTargetAccount($targetService->clientAccount)
                ->setActivationDate($targetService->actual_from)
                ->process();
        }
    }

    /**
     * Процесс отмены связанных с услугой линий без номера, если услуга 7800
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function fallback7800()
    {
        if (!$this->service->line7800_id) {
            return;
        }

        $line7800 = UsageVoip::findOne($this->service->line7800_id);
        Assert::isObject($line7800);

        $this->service = $line7800;
        LogTarifTransfer::fallback($this);

        parent::fallback();
    }

    /**
     * * Процесс отмены переноса связанных с услугой пакетов
     */
    private function fallbackPackages()
    {
        $packages =
            UsageVoipPackage::find()
                ->andWhere(['usage_voip_id' => $this->service->id])
                ->all();

        if (!count($packages)) {
            return;
        }

        foreach ($packages as $package) {
            $package->transferHelper
                ->setTargetAccount($this->service->clientAccount)
                ->fallback();
        }
    }

}
