<?php

namespace app\classes\transfer;

use Yii;
use app\models\usages\UsageInterface;
use app\models\ClientAccount;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use yii\db\ActiveRecord;

/**
 * Класс переноса услуг типа "Виртуальная АТС"
 */
class VirtpbxServiceTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageVirtpbx[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return
            UsageVirtpbx::find()
                ->client($clientAccount->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
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

        return $targetService;
    }
}
