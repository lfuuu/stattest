<?php

namespace app\classes\traits;

use app\models\ClientAccount;
use Yii;
use yii\db\ActiveRecord;

/**
 * Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
 */
trait AddClientAccountFilterTraits
{
    /**
     * Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
     *
     * @param ActiveRecord $filterModel
     * @param array $get
     */
    private function _addClientAccountFilter(ActiveRecord &$filterModel, $get = [])
    {
        if (!$get) {
            $get = Yii::$app->request->get();
        }

        $clientAccountField = $this->getClientAccountField();

        $className = $filterModel->formName();
        if (
            ($clientAccountId = $this->_getCurrentClientAccountId()) &&
            !isset($get[$className][$clientAccountField])
        ) {
            $get[$className][$clientAccountField] = $clientAccountId;
        }

        $filterModel->load($get);
    }

    /**
     * Вернуть ID текущего клиента, если он есть
     *
     * @return int|null
     */
    private function _getCurrentClientAccountId()
    {
        global $fixclient_data;
        if (isset($fixclient_data['id']) && $fixclient_data['id'] > 0) {
            return (int)$fixclient_data['id'];
        }

        return null;
    }

    /**
     * Вернуть текущего клиента, если он есть
     *
     * @return ClientAccount|null
     */
    private function _getCurrentClientAccount()
    {
        $accountId = $this->_getCurrentClientAccountId();
        if (!$accountId || ($clientAccount = ClientAccount::findOne(['id' => $accountId])) === null) {
            Yii::$app->session->setFlash('error',
                Yii::t(
                    'tariff', 'You should {a_start}select a client first{a_finish}',
                    ['a_start' => '<a href="/">', 'a_finish' => '</a>']
                )
            );
            return null;
        }

        return $clientAccount;
    }

    /**
     * Вернуть имя колонки, в которую надо установить фильтр по клиенту
     *
     * @return string
     */
    protected function getClientAccountField()
    {
        return 'client_account_id';
    }
}