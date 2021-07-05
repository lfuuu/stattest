<?php

namespace app\classes;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\UserGrantGroups;
use app\models\UserRight;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffResource;
use yii\console\Exception;

class Migration extends \yii\db\Migration
{
    public $migrationPath;

    /**
     * @param $sql
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\Exception
     */
    public function executeRaw($sql)
    {
        $pdo = $this->db->getMasterPdo();
        try {
            $pdo->exec($sql);
        } catch (\Exception $e) {
            throw $this->db->getSchema()->convertException($e, mb_substr($sql, 0, 255));
        }
    }

    /**
     * @param $fileName
     * @throws Exception
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\Exception
     */
    public function executeFile($fileName)
    {
        $sql = $this->readFile($fileName);
        $this->executeRaw($sql);
    }

    /**
     * @param $fileName
     * @throws Exception
     */
    public function executeSqlFile($fileName)
    {
        if (!preg_match_all('/host=([\w\.\-]+);dbname=(\w+)/i', $this->db->dsn, $matches)) {
            #mysql:host=mysql-db;dbname=nispd
            throw new Exception("Bad database configuration {$this->db->dsn}");
        }

        $dbHost = $matches[1][0];
        $dbName = $matches[2][0];
        $dbUser = $this->db->username;
        $dbPass = $this->db->password;
        $fullFileName = $this->getFullFileName($fileName);

        $command = "mysql -h $dbHost -u $dbUser";
        if ($dbPass) {
            $command .= " -p$dbPass";
        }
        $command .= " $dbName < $fullFileName";

        system($command, $result);
        if ($result !== 0) {
            throw new Exception("Error executing sql file");
        }
    }

    /**
     * @param $tableName
     */
    public function applyFixture($tableName)
    {
        $fixture = new Fixture();
        $fixture->tableName = $tableName;
        $fixture->dataFile = $this->getFullFileName($tableName . '.php');
        $fixture->load();
    }

    /**
     * @param $fileName
     * @return false|string
     * @throws Exception
     */
    private function readFile($fileName)
    {
        $fullFileName = $this->getFullFileName($fileName);

        if (!file_exists($fullFileName)) {
            throw new Exception('Can\'t read file. Not exists. ' . $fullFileName);
        }

        if (!is_readable($fullFileName)) {
            throw new Exception('Can\'t read file. Not readable. ' . $fullFileName);
        }

        return file_get_contents($fullFileName);
    }

    private function getFullFileName($fileName)
    {
        return $this->migrationPath . '/data/' . get_class($this) . '/' . $fileName;
    }


    /**
     * Добавить права
     *
     * @param string $resource
     * @param string $name
     */
    protected function addUserRights($resource, $name)
    {
        $this->insert(UserRight::tableName(), [
            'resource' => $resource,
            'comment' => $name,
            'values' => 'read,write',
            'values_desc' => 'чтение,редактирование',
            'order' => 0,
        ]);

        $this->insert(UserGrantGroups::tableName(), [
            'name' => UserGrantGroups::GROUP_MANAGER,
            'resource' => $resource,
            'access' => 'read,write',
        ]);
        $this->insert(UserGrantGroups::tableName(), [
            'name' => UserGrantGroups::GROUP_ACCOUNT_MANAGER,
            'resource' => $resource,
            'access' => 'read,write',
        ]);
        $this->insert(UserGrantGroups::tableName(), [
            'name' => UserGrantGroups::GROUP_ADMIN,
            'resource' => $resource,
            'access' => 'read,write',
        ]);
    }

    /**
     * Удалить права
     *
     * @param string $resource
     */
    protected function dropUserRights($resource)
    {
        $this->delete(UserRight::tableName(), [
            'resource' => $resource
        ]);

        $this->delete(UserGrantGroups::tableName(), [
            'resource' => $resource,
        ]);
    }


    /**
     * Добавляем новый ресурс в У-услугу
     *
     * @param $serviceTypeId
     * @param $resourceId
     * @param $resourceData
     * @throws ModelValidationException
     * @throws \Exception
     */
    public function insertResource($serviceTypeId, $resourceId, $resourceData, $prices = [], $isOption = true)
    {
        $this->insert(ResourceModel::tableName(), $resourceData + [
                'id' => $resourceId,
                'service_type_id' => $serviceTypeId,
            ]);

        $tariffQuery = Tariff::find()->where([
            'service_type_id' => $serviceTypeId,
        ]);

        $tariffPeriods = [];
        /** @var Tariff $tariff */
        foreach ($tariffQuery->each() as $tariff) {
            $tariffPeriods = array_merge($tariffPeriods, array_map(
                function ($tariffPeriod) {
                    return $tariffPeriod->id;
                },
                $tariff->tariffPeriods
            ));

            if ($tariff->getTariffResource($resourceId)->exists()) {
                // already
                continue;
            }

            $price = $isOption ? 0 : 1;

            if ($prices && isset($prices[$tariff->currency_id])) {
                $price = $prices[$tariff->currency_id];
            }

            $this->insert(TariffResource::tableName(), [
                'amount' => 0,
                'price_per_unit' => $price,
                'price_min' => 0,
                'resource_id' => $resourceId,
                'tariff_id' => $tariff->id,
            ]);
        }

        if (!$isOption) {
            return;
        }

        $query = AccountTariff::find()->where([
            'service_type_id' => $serviceTypeId,
            'tariff_period_id' => $tariffPeriods,
        ]);

        $count = $query->count();
        echo PHP_EOL . 'count: ' . $query->count();
        echo PHP_EOL;

        $cnt = 0;
        $now = (new \DateTime('now'));
        $rc = null;
        /** @var AccountTariff $accountTariff */
        foreach ($query->each() as $accountTariff) {
            if ($accountTariff->getAccountTariffResourceLogsAll()->where(['resource_id' => $resourceId])->exists()) {
                continue;
            }

            $actualFromStr = $accountTariff->getAccountTariffResourceLogs()->min('actual_from_utc');

            if (!$actualFromStr) {
                $actualFromStr = $accountTariff->getAccountTariffLogs()->min('actual_from_utc');
            }

            $atResourceLog = new AccountTariffResourceLog();
            $atResourceLog->isAllowSavingInPast = true;
            $atResourceLog->account_tariff_id = $accountTariff->id;
            $atResourceLog->resource_id = $resourceId;
            $atResourceLog->amount = 0;
            $atResourceLog->actual_from_utc = $actualFromStr;
            $atResourceLog->sync_time = DateTimeZoneHelper::getUtcDateTime()->format(DateTimeZoneHelper::DATETIME_FORMAT);

            if (!$atResourceLog->save()) {
                throw new ModelValidationException($atResourceLog);
            }

            if ($count <= 10) {
                continue;
            }

            $pcn = round((++$cnt / round($count / 100)), 2);
            $lenCount = strlen((string)$count);

            echo "\r" . $count . ' / ' . str_pad($cnt, $lenCount, ' ', STR_PAD_RIGHT) . ' ' . str_pad($pcn . ' %', 9);
            echo '[';
            echo str_pad(str_repeat('=', (int)$pcn + 1) . '>', 100);
            echo '] ';

            if ($pcn * 100 % 5 == 0) {
                $diff = (new \DateTime())->getTimestamp() - $now->getTimestamp();
                if ($diff > 0 && $pcn > 0) {
                    $rc = (new \DateTime())
                        ->setTime(0, 0, 0)
                        ->modify('+' . round((($diff * 100) / $pcn)) . ' second')
                        ->format('H:i:s');

                    $rc .= ' / ' . (new \DateTime())
                            ->setTime(0, 0, 0)
                            ->modify('+' . $diff . ' second')
                            ->format('H:i:s');
                }
            }

            echo $rc;
        }
    }

    public function deleteResource($resourceId)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->delete(TariffResource::tableName(), [
                'resource_id' => $resourceId
            ]);

            $this->delete(AccountTariffResourceLog::tableName(), [
                'resource_id' => $resourceId
            ]);

            $this->delete(ResourceModel::tableName(), [
                'id' => $resourceId
            ]);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }
    }


}