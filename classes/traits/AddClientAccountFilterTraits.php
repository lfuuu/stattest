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
    private function addClientAccountFilter(ActiveRecord &$filterModel, $get = [])
    {
        if (!$get) {
            $get = Yii::$app->request->get();
        }
        $clientAccountField = $this->getClientAccountField();

        $className = $filterModel->formName();
        if (
            ($clientAccountId = $this->getCurrentClientAccountId()) &&
            !isset($get[$className][$clientAccountField])
        ) {
            $get[$className][$clientAccountField] = $clientAccountId;
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
            return (int)$fixclient_data['id'];
        }

        return null;
    }

    /**
     * Вернуть имя колонки, в которую надо установить фильтр по клиенту
     * @return string
     */
    protected function getClientAccountField()
    {
        return 'client_account_id';
    }
}