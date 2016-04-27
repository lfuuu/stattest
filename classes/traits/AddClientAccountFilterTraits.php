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

        // если выбран клиент - принудительно выставить фильтр по нему
        global $fixclient_data;
        if (isset($fixclient_data['id']) && $fixclient_data['id'] > 0) {
            $className = $filterModel->formName();
            $get[$className]['client_account_id'] = $fixclient_data['id'];
        }

        $filterModel->load($get);
    }
}