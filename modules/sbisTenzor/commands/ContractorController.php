<?php

namespace app\modules\sbisTenzor\commands;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\ContractorInfo;
use app\modules\sbisTenzor\helpers\SBISInfo;
use app\modules\sbisTenzor\models\SBISDocument;
use app\modules\sbisTenzor\models\SBISOrganization;
use app\widgets\ConsoleProgress;
use Yii;
use yii\console\Controller;

class ContractorController extends Controller
{
    /**
     * Запрос на получение списка клиентов, работающих с ЭДО
     *
     * @return \app\queries\ClientAccountQuery
     */
    protected function getClientsToUpdate()
    {
        return ClientAccount::find()
            ->active()
            ->with('clientContractModel.clientContragent')
            ->andWhere(['NOT', ['exchange_group_id' => null]]);
    }
    /**
     * Запрос на получение списка клиентов, которые могут работать с ЭДО
     *
     * @return \app\queries\ClientAccountQuery
     */
    protected function getClientsToFetch()
    {
        return ClientAccount::find()
            ->from(['client' => ClientAccount::tableName()])
            ->active()
            //->with('clientContractModel.clientContragent')
            ->innerJoinWith('clientContractModel cc', false)
            ->where(['cc.organization_id' => SBISOrganization::find()
                ->select('organization_id')
                ->where(['is_active' => true])])
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * Обновить информацию по контрагентам
     */
    public function actionUpdate()
    {
        foreach ($this->getClientsToUpdate()->each() as $client) {
            /** @var ClientAccount $client */
            try {
                SBISInfo::getExchangeIntegrationId($client, true);
            } catch (\Exception $e) {
                Yii::error($e);
                $errorText = sprintf(
                    'Ошибка обновления данных по контрагенту (client id: %s): %s',
                    $client->id,
                    $e->getMessage()
                );
                Yii::error($errorText, SBISDocument::LOG_CATEGORY);

                echo $errorText . PHP_EOL;
            }
        }
    }

    /**
     * Обновить информацию по контрагентам
     *
     * @param int $updateClientExchangeGroup
     */
    public function actionFetch($updateClientExchangeGroup = 0)
    {
        $count = $this->getClientsToFetch()->count();
        $progress = new ConsoleProgress($count, function ($string) {
            echo $string;
        });

        $allErrors = [];
        foreach ($this->getClientsToFetch()->each() as $client) {
            $progress->nextStep();

            /** @var ClientAccount $client */
            try {
                $contractorInfo = ContractorInfo::get($client, null, true);

                if (
                    //!$client->exchange_group_id &&
                    $updateClientExchangeGroup &&
                    $contractorInfo->isRoamingEnabled()
                ) {
                    $client->exchange_group_id = $contractorInfo->getExchangeGroupIdDefault();
                    if (!$client->save()) {
                        throw new ModelValidationException($client);
                    }
                }
            } catch (\Exception $e) {
                Yii::error($e);
                $errorText = sprintf(
                    'Ошибка при получении данных по контрагенту (client id: %s): %s',
                    $client->id,
                    $e->getMessage()
                );
                Yii::error($errorText, SBISDocument::LOG_CATEGORY);

                $allErrors[] = $errorText;
            }
        }
        echo PHP_EOL;

        if ($allErrors) {
            echo 'Errors:' . PHP_EOL;
            echo implode(PHP_EOL, $allErrors);
            echo PHP_EOL;
        }
    }
}
