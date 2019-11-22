<?php

namespace app\modules\sbisTenzor\commands;

use app\models\ClientAccount;
use app\modules\sbisTenzor\helpers\SBISInfo;
use app\modules\sbisTenzor\models\SBISDocument;
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
            ->with('clientContractModel.clientContragent')
            ->andWhere(['NOT', ['exchange_group_id' => null]]);
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
            }
        }
    }
}
