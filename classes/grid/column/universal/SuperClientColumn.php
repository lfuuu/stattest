<?php

namespace app\classes\grid\column\universal;

use Yii;
use app\models\ClientAccount;

class SuperClientColumn extends StringColumn
{

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return mixed
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $clientAccountId = $this->getDataCellValue($model, $key, $index);
        $clientAccount = ClientAccount::findOne($clientAccountId);

        return is_null($clientAccount) ? $clientAccountId : $clientAccount->superClient->name;
    }

}