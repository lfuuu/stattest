<?php

namespace app\modules\nnp\commands;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use Yii;
use yii\console\Controller;

/**
 * Группировка регионов
 */
class AccountTariffLightController extends Controller
{
    /**
     * Конвертировать УУ пакетов телефонии в NNP
     * @return int
     */
    public function actionConvert()
    {
        $db = Yii::$app->db;

        $clientAccountTableName = ClientAccount::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $serviceTypeId = ServiceType::ID_VOIP_PACKAGE_CALLS;
        $versionBillerUniversal = ClientAccount::VERSION_BILLER_UNIVERSAL;

        $selectSQL = <<<SQL
            SELECT
                account_tariff_log.id,
                account_tariff_log.account_tariff_id
            FROM
                {$clientAccountTableName} clients,
                {$accountTariffTableName} account_tariff,
                {$accountTariffLogTableName} account_tariff_log
            WHERE
                clients.account_version = {$versionBillerUniversal}
                AND clients.id = account_tariff.client_account_id
                AND account_tariff.service_type_id = {$serviceTypeId}
                AND account_tariff.id = account_tariff_log.account_tariff_id
SQL;
        $dataReader = $db->createCommand($selectSQL)
            ->query();
        $log = [];
        foreach ($dataReader as $row) {

            if (isset($log[$row['account_tariff_id']])) {
                // уже обработали эту услугу по другому логу тарифов
                continue;
            }
            $log[$row['account_tariff_id']] = true;

            try {
                $accountTariffLog = AccountTariffLog::findOne(['id' => $row['id']]);
                $accountTariffLog->trigger(ActiveRecord::EVENT_AFTER_UPDATE);
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }

        }
    }
}
