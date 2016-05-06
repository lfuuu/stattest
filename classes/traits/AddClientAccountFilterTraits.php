<?php
namespace app\classes\traits;

use Yii;
use yii\db\ActiveRecord;

/**
 * Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
 */
trait AddClientAccountFilterTraits
{
    /**
     * Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
     * @param ActiveRecord $filterModel
     */
    private function addClientAccountFilter(ActiveRecord &$filterModel)
    {
        $get = Yii::$app->request->get();

        if ($clientAccountId = $this->getCurrentClientAccountId()) {
            $className = $filterModel->formName();
            $get[$className]['client_account_id'] = $clientAccountId;
        }

        $filterModel->load($get);
    }

    /**
     * Вернуть текущего клиента, если он есть
     * @return int|null
     */
    private function getCurrentClientAccountId()
    {
        global $fixclient_data;
        if (isset($fixclient_data['id']) && $fixclient_data['id'] > 0) {
            return (int) $fixclient_data['id'];
        }

        return null;
    }
}