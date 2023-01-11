<?php

namespace app\modules\sim\commands;

use app\modules\sim\models\Card;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiProfile;
use app\modules\sim\models\RegionSettings;
use app\modules\sim\models\Registry;
use app\modules\sim\classes\RegistryState;
use app\modules\sim\forms\registry\CommandForm;
use yii\console\Controller;
use yii\db\Command;
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
            ->where(['state' => [RegistryState::STARTED]])
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
            $this->logLine('!!! UNKNOWN SIM CARDS !!!');
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

    /**
     * @param Command $command
     * @param int $isProcess
     * @return int
     * @throws \yii\db\Exception
     */
    protected function execCommandOrPrint(Command $command, $isProcess = 0)
    {
        if ($isProcess) {
            return $command->execute();
        }

        echo '---' .PHP_EOL;
        echo $command->getRawSql() .PHP_EOL;
        echo '---' .PHP_EOL;

        return 0;
    }

    /**
     * Обновление последних использованных ICCID и IMSI
     *
     * @param int $isProcess обработать
     * @throws \yii\db\Exception
     */
    public function actionRemove($isProcess = 0)
    {
        $table = Imsi::tableName();
        $sqlImsi = <<<SQL
DELETE FROM $table where iccid::text not like '8970137%' and iccid NOT IN (8970120621904309995, 8970120621904309996, 8970120621904309997, 8970120621904309998);
DELETE FROM $table where iccid::text like '897013768%';
SQL;
        $commandImsi = Imsi::getDb()->createCommand($sqlImsi);

        $table = Card::tableName();
        $sqlCards = <<<SQL
DELETE FROM $table where iccid::text not like '8970137%' and iccid NOT IN (8970120621904309995, 8970120621904309996, 8970120621904309997, 8970120621904309998);
DELETE FROM $table where iccid::text like '897013768%'; 
SQL;
        $commandCards = Card::getDb()->createCommand($sqlCards);

        $result = false;
        $errorText = '';
        $db = Card::getDb();
        $transaction = $db->beginTransaction();
        try {
            $count = $this->execCommandOrPrint($commandImsi, $isProcess);
            $this->logLine('Imsis deleted: ' . $count);

            $count = $this->execCommandOrPrint($commandCards, $isProcess);
            $this->logLine('Cards deleted: ' . $count);

            $transaction->commit();
            $result = true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $errorText = $e->getMessage();
        }

        $this->logLine($result ? 'Success!' : 'Error: ' . $errorText);
    }

    /**
     * Обновление последних использованных ICCID и IMSI
     *
     * @param int $isProcess обработать
     * @throws \yii\db\Exception
     */
    public function actionUpdateLastUsed($isProcess = 0)
    {
        $this->logLine('--------------------------------------------------');

        $iccidByRegion = Card::find()
            ->select(['MAX(iccid)', 'region_id'])
            ->andWhere('region_id IS NOT NULL')
            ->groupBy('region_id')
            ->indexBy('region_id')
            ->column();
        ;
        $this->logLine('Regions of cards found: ' . count($iccidByRegion));

        $iccidUpdates = [];
        foreach ($iccidByRegion as $regionId => $maxICCID) {
            $rs = RegionSettings::findByRegionId($regionId);
            $lastUsed = intval(substr($maxICCID, -$rs->iccid_range_length));

            $iccidUpdates[$rs->id] = [$rs->id, $lastUsed];
            $this->logLine(sprintf('Region id: %s, region name: %s', $regionId, $rs->region->name));
            $this->logLine($maxICCID . ': ' . $rs->iccid_last_used . ' -> ' . $lastUsed);

            $prefix = strtr($rs->getICCIDPrefix(), [' '=>'']);
            $isPrefixOk = $prefix == substr($maxICCID, 0, strlen($prefix));
            $this->logLine($prefix . ($isPrefixOk ? '' : ' - ICCID prefix does not match!!!'));
            echo PHP_EOL;
        }

        $imsiByRegion = Imsi::find()
            ->from(Imsi::tableName() . ' i')
            ->innerJoin(['c' => Card::tableName()], 'c.iccid = i.iccid')
            ->select(['MAX(i.imsi)', 'c.region_id'])
            ->andWhere('c.region_id IS NOT NULL')
            ->andWhere(['i.profile_id' => ImsiProfile::ID_MSN_RUS])
            ->groupBy('c.region_id')
            ->indexBy('region_id')
            ->column();
        ;
        $this->logLine('Regions of IMSIs found: ' . count($imsiByRegion));

        $imsiUpdates = [];
        foreach ($imsiByRegion as $regionId => $maxIMSI) {
            $rs = RegionSettings::findByRegionId($regionId);
            $lastUsed = intval(substr($maxIMSI, -$rs->imsi_range_length));

            $imsiUpdates[$rs->id] = [$rs->id, $lastUsed];
            $this->logLine(sprintf('Region id: %s, region name: %s', $regionId, $rs->region->name));
            $this->logLine($maxIMSI . ': ' . $rs->imsi_last_used . ' -> ' . $lastUsed);

            $prefix = strtr($rs->getIMSIPrefix(), [' '=>'']);
            $isPrefixOk = $prefix == substr($maxIMSI, 0, strlen($prefix));
            $this->logLine($prefix . ($isPrefixOk ? '' : ' - IMSI prefix does not match!!!'));
            echo PHP_EOL;
        }

        $db = RegionSettings::getDb();

        if ($iccidUpdates) {
            $fields = ['id', 'iccid_last_used'];
            $sql = $db->queryBuilder->batchInsert(RegionSettings::tableName(), $fields, $iccidUpdates);
            $sql .= ' ON DUPLICATE KEY UPDATE `iccid_last_used` = VALUES(`iccid_last_used`)';

            $commandIccid = $db->createCommand($sql);
        }
        if ($imsiUpdates) {
            $fields = ['id', 'imsi_last_used'];
            $sql = $db->queryBuilder->batchInsert(RegionSettings::tableName(), $fields, $imsiUpdates);
            $sql .= ' ON DUPLICATE KEY UPDATE `imsi_last_used` = VALUES(`imsi_last_used`)';

            $commandImsi = $db->createCommand($sql);
        }

        $result = false;
        $errorText = '';
        $transaction = $db->beginTransaction();
        try {
            if ($iccidUpdates) {
                $count = $this->execCommandOrPrint($commandIccid, $isProcess);
                $this->logLine('Last ICCIDs updated: ' . $count);
            }

            if ($imsiUpdates) {
                $count = $this->execCommandOrPrint($commandImsi, $isProcess);
                $this->logLine('Last IMSIs updated: ' . $count);
            }

            $transaction->commit();
            $result = true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $errorText = $e->getMessage();
        }

        $this->logLine($result ? 'Success!' : 'Error: ' . $errorText);
    }
}