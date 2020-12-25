<?php

namespace app\modules\sim\commands;

use app\modules\sim\models\Card;
use app\modules\sim\models\RegionSettings;
use app\modules\sim\models\Registry;
use app\modules\sim\classes\RegistryState;
use app\modules\sim\forms\registry\CommandForm;
use yii\console\Controller;
use yii\db\Expression;

class RegistryController extends Controller
{
    const LIMIT_PER_PROCESS = 1;

    /**
     * Получаем заливки на обработку
     *
     * @return Registry[]
     */
    protected function getImportsToProcess()
    {
        return Registry::find()
            ->with('regionSettings.region')
            ->with('regionSettings.parent')
            ->where(['=', 'state', RegistryState::STARTED])
            ->orderBy([
                'updated_at' => SORT_ASC,
                'created_at' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->limit(self::LIMIT_PER_PROCESS)
            ->all();
    }

    /**
     * @param $message
     * @param bool $lineBreak
     */
    protected function logLine($message, $lineBreak = true)
    {
        echo date("d-m-Y H:i:s") . ": " . $message . ($lineBreak ? PHP_EOL : '');
    }

    /**
     * Заливка
     */
    public function actionProcess()
    {
        foreach (self::getImportsToProcess() as $regionSimHistory) {
            $this->logLine('--------------------------------------------------');
            $this->logLine(sprintf('Got import: id %s, region: %s, count: %s.', $regionSimHistory->id, $regionSimHistory->regionSettings->getRegionFullName(), $regionSimHistory->count));

            $form = new CommandForm(['registry' => $regionSimHistory]);
            $result = $form->process();

            if ($result) {
                $this->logLine(sprintf('ICCIDS: %s, IMSI: %s have been added for region %s.', $form->countICCIDs, $form->countIMSIs, $regionSimHistory->regionSettings->getRegionFullName()));
            }
            $this->logLine($result ? 'Success!' : 'Error: ' . $form->errorText);
        }
    }

    /**
     * Миграция SIM-карт
     */
    public function actionMigrate()
    {
        $this->logLine('--------------------------------------------------');

        $result = false;
        $errorText = '';

        $transaction = Card::getDb()->beginTransaction();
        try {
            $result = $this->migrate();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $errorText = $e->getMessage();
        }

        $this->logLine($result ? 'Success!' : 'Error: ' . $errorText);
    }

    /**
     * @return bool
     */
    protected function migrate()
    {
        $regionSettings = RegionSettings::find()
            ->joinWith(['region'])
            ->where($where = [
                'AND',
                'region_id is not null',
                'parent_id is null',
                'imsi_region_code is not null',

                // temporary
                'regions.id is not null',
            ]);

        $updated = 0;
        foreach ($regionSettings->each() as $regionSetting) {
            /** @var RegionSettings $regionSetting */
            $prefix = $regionSetting->iccid_prefix . $regionSetting->iccid_region_code . $regionSetting->iccid_vendor_code;

            $condition = [
                'AND',
                'region_id is null',
                "iccid::text like ('" . $prefix . "%')"
            ];
            $simCardsQuery = Card::find()
                ->where($condition);

            $count = $simCardsQuery->count();
            $this->logLine(
                sprintf(
                    '%s, region_id #%s: %s',
                    $regionSetting->getRegionFullName(),
                    $regionSetting->region_id,
                    $count
                )
            );

            if ($count) {
                Card::updateAll(['region_id' => $regionSetting->region_id], $condition);
                $updated++;
            }

            $this->logLine('.');
        }

        $this->logLine('Regions updated: ' . $updated);

        // check for unknown
        $simCardsUnknownQuery = Card::find()
            ->select([
                new Expression('COUNT(*) cnt'),
                new Expression('SUBSTRING(iccid::text, 0, 9) substr_iccid'),
            ])
            ->where([
                'AND',
                'region_id is null',
            ])
            ->indexBy('substr_iccid')
            ->groupBy('substr_iccid')
        ;

        if ($statistic = $simCardsUnknownQuery->column()) {
            $this->logLine('***');
            $this->logLine('!!! UNKOWN SIM CARDS !!!');
            foreach ($simCardsUnknownQuery->column() as $iccidPrefix => $count) {
                $this->logLine(
                    sprintf(
                    'SIM-cards with ICCID prefix %s...: %s',
                        $iccidPrefix,
                        $count
                    )
                );
            }
            $this->logLine('***');
        }

        return true;
    }
}